<?php
require_once __DIR__ . '/../services/SmsReviewService.php';
class SmsReviewController extends BaseController
{
    public function index()
    {
        $this->requireRole(['super_admin', 'manager', 'agent']);
        $admin = in_array($_SESSION['user_role'], ['super_admin', 'manager'], true);
        $ids = !empty($_GET['draft']) ? [(int)$_GET['draft']] : [];
        if (!(new SmsReviewService())->pending((int)$_SESSION['user_id'], $admin, $ids, max(1,(int)($_GET['sms_review_page'] ?? 1)))) {
            $return = $_SESSION['sms_review_return'] ?? ($admin ? '/admin/claims' : '/agent/dashboard');
            if (!preg_match('#^/(admin|agent)/[a-zA-Z0-9/_-]+$#', $return)) $return = $admin ? '/admin/claims' : '/agent/dashboard';
            $this->redirect($return);
            return;
        }
        $this->generateCsrfToken();
        $this->view('shared.sms-review', ['title' => 'Review SMS drafts']);
    }

    public function review($id)
    {
        $this->requireRole(['super_admin', 'manager', 'agent']);
        $this->validateCsrf();
        $admin = in_array($_SESSION['user_role'], ['super_admin', 'manager'], true);
        $connection = $this->db->getConnection();
        $decision = (string)($_POST['decision'] ?? 'send');
        try {
            $connection->beginTransaction();
            $queueId = (new SmsReviewService())->review((int)$id, (int)$_SESSION['user_id'], $admin, (string)($_POST['reviewed_token'] ?? ''), (string)($_POST['message'] ?? ''), $decision);
            $connection->commit();
            unset($_SESSION['sms_review_edits'][(int)$id]);
            if ($queueId) {
                try {
                    require_once __DIR__ . '/../services/BulkSmsService.php';
                    $result = (new BulkSmsService())->processQueueByIds([$queueId]);
                    $_SESSION['info'] = !empty($result['submitted_count']) ? 'SMS submitted. Delivery is not yet confirmed.' : 'SMS submission is not confirmed. The saved queue entry has its current status.';
                } catch (Throwable $e) { $_SESSION['warning'] = 'The SMS is queued, but submission is not confirmed. Do not send it again until its status is checked.'; }
            } else $_SESSION['success'] = $decision === 'discard' ? 'SMS discarded. The original action is unchanged.' : 'SMS draft saved. Nothing has been sent.';
        } catch (Throwable $e) {
            if ($connection->inTransaction()) $connection->rollBack();
            $_SESSION['sms_review_edits'][(int)$id] = (string)($_POST['message'] ?? '');
            $_SESSION['error'] = $e instanceof RuntimeException ? $e->getMessage() : 'The SMS could not be saved. Nothing new was sent.';
        }
        if ($decision === 'save' || isset($_SESSION['error'])) {
            $_SESSION['sms_feedback_target'] = '#sms-review-' . (int)$id;
            $this->redirect('/sms-review?draft=' . (int)$id);
        }
        $return = $_SESSION['sms_review_return'] ?? ($admin ? '/admin/claims' : '/agent/dashboard');
        if (!preg_match('#^/(admin|agent)/[a-zA-Z0-9/_-]+$#', $return)) $return = $admin ? '/admin/claims' : '/agent/dashboard';
        $this->redirect($return);
    }
}
