<?php
/**
 * Bulk Email Controller
 * Handles bulk email campaign creation and management
 * 
 * @package Shena\Controllers
 */

require_once __DIR__ . '/../services/BulkEmailService.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../core/Database.php';

class BulkEmailController extends BaseController
{
    private $bulkEmailService;
    private $emailService;
    private $memberModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->bulkEmailService = new BulkEmailService();
        $this->emailService = new EmailService();
        $this->memberModel = new Member();
    }
    
    /**
     * Display bulk email campaigns list
     */
    public function index()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        $campaigns = $this->bulkEmailService->getAllCampaigns();
        $templates = $this->bulkEmailService->getTemplates();
        
        $stats = [
            'active_campaigns' => $this->bulkEmailService->getActiveCampaignCount(),
            'sent_today' => $this->bulkEmailService->getSentCountToday(),
            'total_sent' => $this->bulkEmailService->getTotalSent(),
            'failed_count' => $this->bulkEmailService->getFailedCount()
        ];
        
        $data = [
            'title' => 'Email Campaigns - Admin',
            'campaigns' => $campaigns,
            'templates' => $templates,
            'stats' => $stats,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('admin.email-campaigns', $data);
    }
    
    /**
     * Get emails sent today from communications table
     */
    private function getEmailsSentToday()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT COUNT(*) as count 
                FROM communications 
                WHERE type = 'email' 
                AND DATE(sent_at) = CURDATE()
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting emails sent today: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get total emails sent from communications table
     */
    private function getTotalEmailsSent()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT COUNT(*) as count 
                FROM communications 
                WHERE type = 'email'
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting total emails sent: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get failed emails count from communications table
     */
    private function getFailedEmailsCount()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT COUNT(*) as count 
                FROM communications 
                WHERE type = 'email' 
                AND status = 'failed'
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting failed emails count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Create new email campaign
     */
    public function createCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            $input = $this->readRequestData();
            $this->validateRequestCsrf($input);

            $title = trim($input['title'] ?? '');
            $subject = trim($input['subject'] ?? '');
            $message = trim($input['message'] ?? $input['body'] ?? '');
            $targetAudience = $this->normalizeTargetAudience($input['target_audience'] ?? 'all_members');
            $scheduleType = $input['schedule_type'] ?? null;
            $scheduledAt = $input['scheduled_at'] ?? null;
            $sendNow = isset($input['send_now']) || $scheduleType === 'now' || ($input['action'] ?? '') === 'send';

            if (empty($title) || empty($subject) || empty($message)) {
                throw new Exception('Title, subject, and message are required');
            }

            if ($scheduleType === 'scheduled' && empty($scheduledAt)) {
                throw new Exception('Schedule date and time is required for scheduled campaigns');
            }

            $customFilters = $this->extractCustomFilters($input);
            $recipients = $this->bulkEmailService->getRecipients($targetAudience, $customFilters);

            if (empty($recipients)) {
                throw new Exception('No recipients found for the selected audience');
            }

            $campaignId = $this->bulkEmailService->createCampaign([
                'title' => $title,
                'subject' => $subject,
                'message' => $message,
                'target_audience' => $targetAudience,
                'custom_filters' => $customFilters,
                'scheduled_at' => ($scheduleType === 'scheduled' && !$sendNow) ? $scheduledAt : null,
                'total_recipients' => count($recipients),
                'created_by' => $_SESSION['user_id'] ?? 0
            ]);

            if (!$campaignId) {
                throw new Exception('Failed to create email campaign');
            }

            $this->bulkEmailService->addRecipients($campaignId, $recipients);

            if ($sendNow) {
                $result = $this->bulkEmailService->sendCampaign($campaignId);
                $this->json([
                    'success' => true,
                    'message' => "Email campaign created and sent to {$result['sent']} recipient(s), {$result['failed']} failed",
                    'campaign_id' => $campaignId,
                    'sent' => $result['sent'] ?? 0,
                    'failed' => $result['failed'] ?? 0,
                ]);
            }

            $this->json([
                'success' => true,
                'message' => $scheduleType === 'scheduled' ? 'Email campaign scheduled successfully' : 'Email campaign saved as draft',
                'campaign_id' => $campaignId
            ]);
        } catch (Throwable $e) {
            error_log('Email campaign creation error: ' . $e->getMessage());
            $this->jsonError($e->getMessage(), 400);
        }
    }

    public function editCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            $input = $this->readRequestData();
            $this->validateRequestCsrf($input);

            $campaignId = (int)($input['campaign_id'] ?? 0);
            $title = trim($input['title'] ?? '');
            $subject = trim($input['subject'] ?? '');
            $message = trim($input['message'] ?? $input['body'] ?? '');
            $targetAudience = $this->normalizeTargetAudience($input['target_audience'] ?? 'all_members');
            $scheduledAt = !empty($input['scheduled_at']) ? $input['scheduled_at'] : null;

            if ($campaignId <= 0 || $title === '' || $subject === '' || $message === '') {
                throw new Exception('Campaign ID, title, subject, and message are required');
            }

            $customFilters = $this->extractCustomFilters($input);
            $customFilters['email_subject'] = $subject;

            $setClauses = [
                'title = ?',
                'message = ?',
                'target_audience = ?',
                'custom_filters = ?',
                'scheduled_at = ?',
                "status = CASE WHEN ? IS NULL THEN 'draft' ELSE 'scheduled' END",
            ];

            if ($this->bulkMessagesHasColumn('updated_at')) {
                $setClauses[] = 'updated_at = NOW()';
            }

            $sql = "UPDATE bulk_messages
                    SET " . implode(', ', $setClauses) . "
                    WHERE id = ? AND message_type = 'email' AND status IN ('draft', 'scheduled', 'paused')";
            $stmt = Database::getInstance()->getConnection()->prepare($sql);
            $stmt->execute([
                $title,
                $message,
                $targetAudience,
                json_encode($customFilters),
                $scheduledAt,
                $scheduledAt,
                $campaignId
            ]);

            if ($stmt->rowCount() < 1) {
                $check = Database::getInstance()->getConnection()->prepare("SELECT COUNT(*) FROM bulk_messages WHERE id = ? AND message_type = 'email' AND status IN ('draft', 'scheduled', 'paused')");
                $check->execute([$campaignId]);
                if ((int)$check->fetchColumn() > 0) {
                    $this->json(['success' => true, 'message' => 'Email campaign updated successfully']);
                    return;
                }
                throw new Exception('Campaign not found or cannot be edited after sending has started');
            }

            $this->json(['success' => true, 'message' => 'Email campaign updated successfully']);
        } catch (Throwable $e) {
            error_log('Email edit campaign error: ' . $e->getMessage());
            $this->jsonError($e->getMessage(), 400);
        }
    }
    
    /**
     * Send campaign immediately
     */
    public function sendCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }
        
        $input = $this->readRequestData();
        $campaignId = $input['campaign_id'] ?? 0;
        
        if (!$campaignId) {
            $this->jsonError('Campaign ID is required', 400);
            return;
        }
        
        $result = $this->bulkEmailService->sendCampaign($campaignId);
        
        if ($result['success']) {
            $this->json([
                'success' => true,
                'message' => 'Campaign sending started',
                'sent' => $result['sent'] ?? 0,
                'failed' => $result['failed'] ?? 0
            ]);
        } else {
            $this->jsonError($result['message'] ?? 'Failed to send campaign', 500);
        }
    }
    
    /**
     * Cancel a scheduled campaign
     */
    public function cancelCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }
        
        $input = $this->readRequestData();
        $campaignId = $input['campaign_id'] ?? 0;
        
        if (!$campaignId) {
            $this->jsonError('Campaign ID is required', 400);
            return;
        }
        
        $result = $this->bulkEmailService->cancelCampaign($campaignId);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Campaign cancelled successfully']);
        } else {
            $this->jsonError('Failed to cancel campaign', 500);
        }
    }
    
    /**
     * View single campaign details
     */
    public function viewCampaign($id)
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        $campaign = $this->bulkEmailService->getCampaign($id);
        
        if (!$campaign) {
            $_SESSION['error'] = 'Campaign not found';
            header('Location: /admin/communications');
            exit;
        }
        
        $recipients = $this->bulkEmailService->getCampaignRecipients($id);
        
        $stats = $this->bulkEmailService->getCampaignStats($id);

        $this->view('admin.campaign-details', [
            'title' => 'Campaign Details - ' . $campaign['title'],
            'campaign' => $campaign,
            'recipients' => $recipients,
            'stats' => $stats,
            'channel' => 'email',
            'back_url' => '/admin/email-campaigns'
        ]);
    }
    
    /**
     * Get email templates
     */
    public function templates()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        $templates = $this->bulkEmailService->getTemplates();
        
        $this->json(['success' => true, 'templates' => $templates]);
    }
    
    /**
     * Send quick email (single or to selected members)
     */
    public function quickEmail()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }
        
        $recipients = $_POST['recipients'] ?? []; // Array of emails
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if (empty($recipients) || empty($subject) || empty($message)) {
            $this->jsonError('Recipients, subject, and message are required', 400);
            return;
        }
        
        $sent = 0;
        $failed = 0;
        
        foreach ($recipients as $email) {
            try {
                $result = $this->emailService->sendEmail($email, $subject, $message, true);
                if ($result) {
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (Exception $e) {
                error_log('Quick email error: ' . $e->getMessage());
                $failed++;
            }
        }
        
        $this->json([
            'success' => true,
            'message' => "Sent {$sent} emails, {$failed} failed",
            'sent' => $sent,
            'failed' => $failed
        ]);
    }
    
    /**
     * Pause a running campaign
     */
    public function pauseCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }
        
        $input = $this->readRequestData();
        $campaignId = $input['campaign_id'] ?? 0;
        
        if (!$campaignId) {
            $this->jsonError('Campaign ID is required', 400);
            return;
        }
        
        $result = $this->bulkEmailService->pauseCampaign($campaignId);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Campaign paused successfully']);
        } else {
            $this->jsonError('Failed to pause campaign', 500);
        }
    }
    
    /**
     * Reschedule a campaign
     */
    public function reschedule()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }
        
        $input = $this->readRequestData();
        $campaignId = $input['campaign_id'] ?? 0;
        $scheduledAt = $input['scheduled_at'] ?? null;
        
        if (!$campaignId || !$scheduledAt) {
            $this->jsonError('Campaign ID and scheduled date are required', 400);
            return;
        }
        
        $result = $this->bulkEmailService->rescheduleCampaign($campaignId, $scheduledAt);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Campaign rescheduled successfully']);
        } else {
            $this->jsonError('Failed to reschedule campaign', 500);
        }
    }
    
    /**
     * Retry failed recipients
     */
    public function retryFailed()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }
        
        $input = $this->readRequestData();
        $campaignId = $input['campaign_id'] ?? 0;
        
        if (!$campaignId) {
            $this->jsonError('Campaign ID is required', 400);
            return;
        }
        
        $result = $this->bulkEmailService->retryFailedRecipients($campaignId);
        
        $this->json([
            'success' => true,
            'message' => "Retried {$result['retried']} failed emails",
            'sent' => $result['sent'] ?? 0,
            'failed' => $result['failed'] ?? 0
        ]);
    }

    private function readRequestData()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
            return is_array($data) ? $data : [];
        }

        return $_POST;
    }

    private function jsonError($message, $code = 400)
    {
        $this->json(['success' => false, 'message' => $message, 'error' => $message], $code);
    }

    private function validateRequestCsrf(array $input)
    {
        if (isset($_SESSION['csrf_token']) && isset($input['csrf_token']) && hash_equals($_SESSION['csrf_token'], $input['csrf_token'])) {
            return;
        }

        throw new Exception('CSRF token mismatch');
    }

    private function normalizeTargetAudience($targetAudience)
    {
        $map = [
            'active_only' => 'active',
            'inactive' => 'custom',
            'pending' => 'custom',
            'defaulters' => 'defaulted',
        ];

        return $map[$targetAudience] ?? $targetAudience;
    }

    private function extractCustomFilters(array $input)
    {
        $filters = [];

        if (($input['target_audience'] ?? '') === 'inactive') {
            $filters['member_status'] = 'inactive';
        } elseif (($input['target_audience'] ?? '') === 'pending') {
            $filters['member_status'] = 'pending';
        }

        foreach (['filter_status' => 'member_status', 'filter_package' => 'package', 'filter_joined_after' => 'joined_after', 'filter_joined_before' => 'joined_before'] as $inputKey => $filterKey) {
            if (!empty($input[$inputKey])) {
                $filters[$filterKey] = $input[$inputKey];
            }
        }

        return $filters;
    }

    private function bulkMessagesHasColumn(string $column): bool
    {
        try {
            $stmt = Database::getInstance()->getConnection()->prepare('SHOW COLUMNS FROM bulk_messages LIKE ?');
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        } catch (Throwable $e) {
            error_log('bulk_messages column check failed: ' . $e->getMessage());
            return false;
        }
    }
}
