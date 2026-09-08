<?php
if (!in_array($_SESSION['user_role'] ?? '', ['super_admin', 'manager', 'agent'], true) || !empty($GLOBALS['sms_review_rendered'])) return;
$ids = array_values($_SESSION['sms_review_ids'] ?? []);
if (!empty($smsReviewAll)) $ids = !empty($_GET['draft']) ? [(int)$_GET['draft']] : [];
if (empty($smsReviewAll) && !$ids) return;
$GLOBALS['sms_review_rendered'] = true;
require_once __DIR__ . '/../../../app/services/SmsReviewService.php';
try {
    $page = max(1, (int)($_GET['sms_review_page'] ?? 1));
    $drafts = (new SmsReviewService())->pending((int)$_SESSION['user_id'], in_array($_SESSION['user_role'], ['super_admin', 'manager'], true), $ids, $page);
    unset($_SESSION['sms_review_ids']);
} catch (Throwable $e) {
    echo '<p role="alert">SMS draft review is unavailable. Apply the SMS review schema migration before using this feature.</p>';
    return;
}
?>
<div class="sms-review-panel" id="sms-review-inbox">
    <h2>SMS drafts to review</h2>
    <p>These actions are already saved. Edit and review each SMS before sending it.</p>
    <?php if (!$drafts): ?><p>No pending SMS drafts.</p><?php endif; ?>
    <?php foreach (array_slice($drafts, 0, 25) as $draft): ?>
        <?php
        $smsComposerId = 'sms-review-' . (int)$draft['id'];
        $smsTitle = $draft['source'] . ' - ' . $draft['created_at'];
        $smsMessage = $_SESSION['sms_review_edits'][(int)$draft['id']] ?? $draft['message'];
        unset($_SESSION['sms_review_edits'][(int)$draft['id']]); $smsPhone = $draft['phone_number'];
        $smsAction = '/sms-review/' . (int)$draft['id'];
        $smsHidden = ['reviewed_token' => SmsReviewService::token($draft)];
        $smsAllowSave = true;
        include __DIR__ . '/sms-composer.php';
        ?>
    <?php endforeach; ?>
    <?php if ($page > 1): ?><a href="/sms-review?sms_review_page=<?= $page - 1 ?>">Previous drafts</a><?php endif; ?>
    <?php if (count($drafts) > 25): ?><a href="/sms-review?sms_review_page=<?= $page + 1 ?>">Next drafts</a><?php endif; ?>
    <?php if ($ids): ?><a href="/sms-review">All pending SMS drafts</a><?php endif; ?>
</div>
