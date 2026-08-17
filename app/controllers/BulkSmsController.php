<?php
/**
 * Bulk SMS Controller
 * Handles bulk SMS campaign creation and management
 * 
 * @package Shena\Controllers
 */

require_once __DIR__ . '/../services/BulkSmsService.php';
require_once __DIR__ . '/../services/SmsService.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../core/Database.php';

class BulkSmsController extends BaseController
{
    private $bulkSmsService;
    private $smsService;
    private $memberModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->bulkSmsService = new BulkSmsService();
        $this->smsService = new SmsService();
        $this->memberModel = new Member();
    }
    
    /**
     * Display bulk SMS campaigns list
     */
    public function index()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        $quickFilters = [
            'status' => $_GET['quick_status'] ?? 'all',
            'search' => trim($_GET['quick_search'] ?? ''),
            'date_from' => trim($_GET['quick_date_from'] ?? ''),
            'date_to' => trim($_GET['quick_date_to'] ?? ''),
        ];
        $quickPage = max(1, (int)($_GET['quick_page'] ?? 1));
        $quickPerPage = 25;
        $quickTotal = $this->bulkSmsService->getQueueItemsCount($quickFilters);
        $quickOffset = ($quickPage - 1) * $quickPerPage;

        $campaigns = $this->bulkSmsService->getAllCampaigns();
        $editCampaignToOpen = null;
        $requestedEditCampaignId = (int)($_GET['edit_campaign'] ?? 0);
        if ($requestedEditCampaignId > 0) {
            $editCampaignToOpen = $this->bulkSmsService->getCampaignById($requestedEditCampaignId);
            if ($editCampaignToOpen && ($editCampaignToOpen['message_type'] ?? '') !== 'sms') {
                $editCampaignToOpen = null;
            }
        }
        $queue_items = $this->bulkSmsService->getQueueItems($quickFilters, $quickPerPage, $quickOffset);
        $templates = $this->bulkSmsService->getTemplates();
        
        $stats = [
            'active_campaigns' => $this->bulkSmsService->getActiveCampaignCount(),
            'sent_today' => $this->bulkSmsService->getSentCountToday(),
            'queue_pending' => $this->bulkSmsService->getQueuePendingCount(),
            'sms_credits' => $this->bulkSmsService->getSmsCredits(),
            'total_sent' => $this->bulkSmsService->getTotalSentCount(),
            'failed_count' => $this->bulkSmsService->getFailedCount()
        ];
        
        $data = [
            'title' => 'SMS Campaigns - Shena Companion',
            'campaigns' => $campaigns,
            'queue_items' => $queue_items,
            'quick_filters' => $quickFilters,
            'quick_pagination' => [
                'current_page' => $quickPage,
                'total_pages' => max(1, (int)ceil($quickTotal / $quickPerPage)),
                'total_items' => $quickTotal,
                'per_page' => $quickPerPage,
            ],
            'templates' => $templates,
            'stats' => $stats,
            'edit_campaign_to_open' => $editCampaignToOpen
        ];
        
        $this->view('admin.sms-campaigns', $data);
    }
    
    /**
     * Get SMS sent today from communications table
     */
    private function getSmsSentToday()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT COUNT(*) as count 
                FROM communications 
                WHERE type = 'sms' 
                AND DATE(sent_at) = CURDATE()
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting SMS sent today: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get total SMS sent from communications table
     */
    private function getTotalSmsSent()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT COUNT(*) as count 
                FROM communications 
                WHERE type = 'sms'
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting total SMS sent: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get failed SMS count from communications table
     */
    private function getFailedSmsCount()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT COUNT(*) as count 
                FROM communications 
                WHERE type = 'sms' 
                AND status = 'failed'
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting failed SMS count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Display campaign creation form
     */
    public function create()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        // Get member statistics for targeting
        $stats = $this->getMemberStats();
        
        $this->render('admin/bulk-sms/create', [
            'stats' => $stats,
            'pageTitle' => 'Create SMS Campaign'
        ]);
    }
    
    /**
     * Store new campaign
     */
    public function store()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/bulk-sms/create');
            return;
        }
        
        // Validate input
        $errors = $this->validateCampaignData($_POST);
        
        if (!empty($errors)) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['errors'] = $errors;
            redirect('/admin/bulk-sms/create');
            return;
        }
        
        // Prepare campaign data
        $campaignData = [
            'title' => $_POST['title'],
            'message' => $_POST['message'],
            'target_audience' => $_POST['target_audience'],
            'scheduled_at' => !empty($_POST['scheduled_at']) ? $_POST['scheduled_at'] : null
        ];
        
        // Add custom filters if target is 'custom'
        if ($_POST['target_audience'] === 'custom') {
            $customFilters = [];
            
            if (!empty($_POST['filter_package'])) {
                $customFilters['package'] = $_POST['filter_package'];
            }
            if (!empty($_POST['filter_status'])) {
                $customFilters['status'] = $_POST['filter_status'];
            }
            if (!empty($_POST['filter_county'])) {
                $customFilters['county'] = $_POST['filter_county'];
            }
            if (!empty($_POST['filter_joined_after'])) {
                $customFilters['joined_after'] = $_POST['filter_joined_after'];
            }
            if (!empty($_POST['filter_joined_before'])) {
                $customFilters['joined_before'] = $_POST['filter_joined_before'];
            }
            
            $campaignData['custom_filters'] = $customFilters;
        }
        
        // Create campaign
        $campaignId = $this->bulkSmsService->createCampaign($campaignData, $_SESSION['user_id']);
        
        if (!$campaignId) {
            $this->setFlashMessage('Failed to create campaign', 'error');
            redirect('/admin/bulk-sms/create');
            return;
        }
        
        // Get recipients and queue them
        $recipients = $this->bulkSmsService->getRecipients(
            $campaignData['target_audience'],
            $campaignData['custom_filters'] ?? []
        );
        
        if (empty($recipients)) {
            $this->setFlashMessage('No recipients found for the selected criteria', 'warning');
            redirect('/admin/bulk-sms/view/' . $campaignId);
            return;
        }
        
        // Queue recipients
        $this->bulkSmsService->queueRecipients($campaignId, $recipients);
        
        $recipientCount = count($recipients);
        $this->setFlashMessage("Campaign created with {$recipientCount} recipients", 'success');
        redirect('/admin/bulk-sms/view/' . $campaignId);
    }
    
    /**
     * Display campaign details
     */
    public function show($campaignId)
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        $campaign = $this->bulkSmsService->getCampaignById($campaignId);
        
        if (!$campaign) {
            $this->setFlashMessage('Campaign not found', 'error');
            redirect('/admin/bulk-sms');
            return;
        }
        
        $stats = $this->bulkSmsService->getCampaignStats($campaignId);
        
        $this->render('admin/bulk-sms/view', [
            'campaign' => $campaign,
            'stats' => $stats,
            'pageTitle' => 'Campaign: ' . $campaign['title']
        ]);
    }
    
    /**
     * Send campaign immediately
     */
    public function send($campaignId)
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/bulk-sms/view/' . $campaignId);
            return;
        }
        
        $campaign = $this->bulkSmsService->getCampaignById($campaignId);
        
        if (!$campaign) {
            $this->setFlashMessage('Campaign not found', 'error');
            redirect('/admin/bulk-sms');
            return;
        }
        
        if ($campaign['status'] !== 'draft') {
            $this->setFlashMessage('Only draft campaigns can be sent', 'error');
            redirect('/admin/bulk-sms/view/' . $campaignId);
            return;
        }
        
        // Send campaign (in batches)
        $batchSize = isset($_POST['batch_size']) ? (int)$_POST['batch_size'] : 50;
        $result = $this->bulkSmsService->sendCampaign($campaignId, $batchSize);
        
        if ($result['success']) {
        $message = "Submitted: {$result['sent_count']}, Failed: {$result['failed_count']}, Pending: {$result['pending_count']}";
            $this->setFlashMessage($message, 'success');
        } else {
            $this->setFlashMessage($result['error'], 'error');
        }
        
        redirect('/admin/bulk-sms/view/' . $campaignId);
    }
    
    /**
     * Delete campaign
     */
    public function delete($campaignId)
    {
        $this->requireRole(['admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/bulk-sms');
            return;
        }
        
        if ($this->bulkSmsService->deleteCampaign($campaignId)) {
            $this->setFlashMessage('Campaign deleted successfully', 'success');
        } else {
            $this->setFlashMessage('Failed to delete campaign (only draft campaigns can be deleted)', 'error');
        }
        
        redirect('/admin/bulk-sms');
    }
    
    /**
     * Preview recipients for campaign
     * Returns JSON response
     */
    public function previewRecipients()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        header('Content-Type: application/json');
        
        $rawTargetAudience = $_GET['target_audience'] ?? 'all_members';
        $targetAudience = $this->normalizeTargetAudience($rawTargetAudience);
        $customFilters = $this->extractCustomFilters($_GET);

        if (!empty($_GET['filter_county'])) {
            $customFilters['county'] = $_GET['filter_county'];
        }
        
        $recipients = $this->bulkSmsService->getRecipients($targetAudience, $customFilters);
        
        echo json_encode([
            'count' => count($recipients),
            'sample' => array_slice($recipients, 0, 10) // First 10 for preview
        ]);
        exit;
    }

    public function previewCampaignRecipient($id)
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');

        try {
            $recipients = $this->bulkSmsService->getCampaignRecipients((int)$id);
            $this->json([
                'success' => true,
                'recipient' => $recipients[0] ?? null,
            ]);
        } catch (Throwable $e) {
            error_log('SMS campaign preview recipient error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to load preview recipient'], 400);
        }
    }
    
    /**
     * Get member statistics for targeting
     * 
     * @return array Statistics
     */
    private function getMemberStats()
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT 
                    COUNT(*) as total_members,
                    COUNT(CASE WHEN m.status = 'active' THEN 1 END) as active_count,
                    COUNT(CASE WHEN m.status = 'grace_period' THEN 1 END) as grace_period_count,
                    COUNT(CASE WHEN m.status = 'defaulted' THEN 1 END) as defaulted_count,
                    COUNT(CASE WHEN m.package = 'individual' OR m.package_key LIKE 'individual_%' THEN 1 END) as individual_count,
                    COUNT(CASE WHEN m.package = 'family' OR m.package_key LIKE 'couple_%' THEN 1 END) as family_count,
                    COUNT(CASE WHEN m.package = 'extended_family_1' OR m.package_key LIKE 'couple_children_%' THEN 1 END) as extended_family_1_count,
                    COUNT(CASE WHEN m.package = 'extended_family_2' OR m.package_key LIKE 'executive_%' THEN 1 END) as extended_family_2_count,
                    COUNT(CASE WHEN np.sms_enabled = 1 THEN 1 END) as sms_enabled_count
                FROM members m
                LEFT JOIN users u ON m.user_id = u.id
                LEFT JOIN notification_preferences np ON u.id = np.user_id";
        
        $stmt = $db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Validate campaign data
     * 
     * @param array $data Form data
     * @return array Validation errors
     */
    private function validateCampaignData($data)
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors['title'] = 'Campaign title is required';
        }
        
        if (empty($data['message'])) {
            $errors['message'] = 'Message content is required';
        } elseif (strlen($data['message']) > 480) {
            $errors['message'] = 'Message cannot exceed 480 characters (3 SMS segments)';
        }
        
        if (empty($data['target_audience'])) {
            $errors['target_audience'] = 'Target audience is required';
        }
        
        if (!empty($data['scheduled_at'])) {
            $scheduledTime = strtotime($data['scheduled_at']);
            if ($scheduledTime < time()) {
                $errors['scheduled_at'] = 'Scheduled time must be in the future';
            }
        }
        
        return $errors;
    }
    
    /**
     * Create new campaign (for SMS campaigns view)
     */
    public function createCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $this->validateCsrf();
            
            // Validate inputs
            $title = $this->sanitizeInput($_POST['title'] ?? '');
            $message = $this->sanitizeInput($_POST['message'] ?? '');
            $rawTargetAudience = $_POST['target_audience'] ?? '';
            $targetAudience = $this->normalizeTargetAudience($rawTargetAudience);
            $action = $_POST['action'] ?? 'draft'; // draft or send
            $sendTime = $_POST['send_time'] ?? 'now';
            $scheduleType = $_POST['schedule_type'] ?? null;
            if ($scheduleType === 'now') {
                $action = 'send';
                $sendTime = 'now';
            } elseif ($scheduleType === 'scheduled') {
                $sendTime = 'scheduled';
            } elseif ($scheduleType === 'draft') {
                $action = 'draft';
                $sendTime = 'draft';
            }
            $scheduledAt = $_POST['scheduled_at'] ?? null;
            
            if (empty($title) || empty($message) || empty($targetAudience)) {
                throw new Exception('Title, message, and target audience are required');
            }
            
            if (strlen($message) > 160) {
                throw new Exception('Message must be 160 characters or less');
            }
            
            // Handle custom filters
            $customFilters = null;
            if ($targetAudience === 'custom') {
                $customFilters = [
                    'package' => $_POST['filter_package'] ?? null,
                    'status' => $_POST['filter_status'] ?? ($rawTargetAudience === 'inactive' || $rawTargetAudience === 'pending' ? $rawTargetAudience : null),
                    'joined_after' => $_POST['filter_joined_after'] ?? null,
                    'joined_before' => $_POST['filter_joined_before'] ?? null,
                ];
            }
            
            // Prepare campaign data
            $campaignData = [
                'title' => $title,
                'message' => $message,
                'target_audience' => $targetAudience,
                'custom_filters' => $customFilters,
                'scheduled_at' => ($sendTime === 'scheduled' && $scheduledAt) ? $scheduledAt : null
            ];
            
            // Create campaign
            $campaignId = $this->bulkSmsService->createCampaign($campaignData, $_SESSION['user_id']);
            
            if (!$campaignId) {
                throw new Exception('Failed to create campaign');
            }
            
            // Get recipients
            $recipients = $this->bulkSmsService->getRecipients($targetAudience, $customFilters ?? []);
            
            if (empty($recipients)) {
                throw new Exception('No recipients found for the selected audience');
            }
            
            // Queue recipients
            $this->bulkSmsService->queueRecipients($campaignId, $recipients);
            
            // If action is 'send', start sending immediately
            if ($action === 'send' && $sendTime === 'now') {
                $this->bulkSmsService->sendCampaign($campaignId);
                $successMsg = 'Campaign created and submitted for delivery tracking. (' . count($recipients) . ' recipients)';
            } elseif ($sendTime === 'scheduled') {
                $successMsg = 'Campaign scheduled successfully for ' . date('M j, Y H:i', strtotime($scheduledAt));
            } else {
                $successMsg = 'Campaign saved as draft';
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $successMsg, 'campaign_id' => $campaignId]);
            exit();

        } catch (Exception $e) {
            error_log('Campaign creation error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }

    private function normalizeTargetAudience($targetAudience)
    {
        $map = [
            'active_only' => 'active',
            'defaulters' => 'defaulted',
            'payment_paid' => 'payment_paid_current',
            'payment_unpaid' => 'payment_unpaid_current',
            'payment_partial' => 'payment_partially_paid',
            'payment_arrears' => 'payment_in_arrears',
            'payment_defaulted' => 'payment_defaulted',
            'inactive' => 'custom',
            'pending' => 'custom',
        ];

        return $map[$targetAudience] ?? $targetAudience;
    }

    public function createCampaignFromPaymentGroup()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $filters = [
                'payment_group' => $this->sanitizeInput($_POST['payment_group'] ?? $_POST['group'] ?? 'unpaid_current'),
                'search' => $this->sanitizeInput($_POST['search'] ?? ''),
                'package' => $this->sanitizeInput($_POST['package'] ?? 'all'),
                'amount_min' => $this->sanitizeInput($_POST['amount_min'] ?? ''),
                'amount_max' => $this->sanitizeInput($_POST['amount_max'] ?? ''),
                'missed_months' => $this->sanitizeInput($_POST['missed_months'] ?? ''),
            ];

            $campaignId = $this->bulkSmsService->createDraftFromPaymentGroup($filters, (int)($_SESSION['user_id'] ?? 0));
            if (!$campaignId) {
                throw new Exception('Failed to create draft SMS campaign');
            }

            $_SESSION['success'] = 'Draft SMS campaign created. Review the message, recipients, and schedule before sending.';
            $this->redirect('/admin/communications/campaign/' . $campaignId);
        } catch (Throwable $e) {
            error_log('Create payment group SMS campaign error: ' . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/admin/payments/breakdown');
        }
    }
    
    /**
     * Send campaign (JSON response)
     */
    public function sendCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $campaignId = $input['campaign_id'] ?? 0;
            
            if (!$campaignId) {
                throw new Exception('Campaign ID is required');
            }

            $campaign = $this->bulkSmsService->getCampaignById($campaignId);
            if ($campaign && ($campaign['status'] ?? '') === 'paused') {
                $this->bulkSmsService->resumePausedCampaignForManualSend((int)$campaignId);
            }
            
            $result = $this->bulkSmsService->sendCampaignUntilComplete($campaignId, 50, 10);
            
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'message' => 'Campaign submitted. Delivery confirmation will update after sync.',
                    'sent_count' => $result['sent_count'],
                    'failed_count' => $result['failed_count'],
                    'pending_count' => $result['pending_count'],
                    'processed_count' => $result['processed_count'] ?? 0
                ]);
            } else {
                throw new Exception($result['error'] ?? 'Failed to send campaign');
            }
            
        } catch (Exception $e) {
            error_log('Send campaign error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Cancel campaign (JSON response)
     */
    public function cancelCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $campaignId = $input['campaign_id'] ?? 0;
            
            if (!$campaignId) {
                throw new Exception('Campaign ID is required');
            }
            
            $result = $this->bulkSmsService->cancelCampaign($campaignId);
            
            if ($result) {
                $this->json(['success' => true, 'message' => 'Campaign cancelled successfully']);
            } else {
                throw new Exception('Failed to cancel campaign');
            }
            
        } catch (Exception $e) {
            error_log('Cancel campaign error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Process SMS queue (JSON response)
     */
    public function processQueue()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');
        
        try {
            $scheduledResult = $this->bulkSmsService->processDueCampaigns(50, 10, 3);
            $queueResult = $this->bulkSmsService->processQueue(100);
            
            $this->json([
                'success' => true,
                'message' => 'Scheduled campaigns and SMS queue processed',
                'campaign_count' => $scheduledResult['campaign_count'] ?? 0,
                'processed_count' => $scheduledResult['processed_count'] ?? 0,
                'sent_count' => ($scheduledResult['sent_count'] ?? 0) + ($queueResult['sent_count'] ?? 0),
                'submitted_count' => ($scheduledResult['submitted_count'] ?? $scheduledResult['sent_count'] ?? 0) + ($queueResult['submitted_count'] ?? $queueResult['sent_count'] ?? 0),
                'delivered_count' => $scheduledResult['delivered_count'] ?? 0,
                'failed_count' => ($scheduledResult['failed_count'] ?? 0) + ($queueResult['failed_count'] ?? 0),
                'pending_count' => $scheduledResult['pending_count'] ?? 0,
                'queue_sent_count' => $queueResult['sent_count'] ?? 0,
                'queue_failed_count' => $queueResult['failed_count'] ?? 0
            ]);
            
        } catch (Exception $e) {
            error_log('Process queue error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $campaignId = (int)($input['campaign_id'] ?? 0);
            $confirmed = !empty($input['confirm_delete']);

            if ($campaignId <= 0 || !$confirmed) {
                throw new Exception('Campaign ID and delete confirmation are required');
            }

            if ($this->bulkSmsService->deleteCampaign($campaignId)) {
                $this->json(['success' => true, 'message' => 'Campaign deleted successfully']);
                return;
            }

            throw new Exception('Campaign not found or cannot be deleted while sending');
        } catch (Throwable $e) {
            error_log('Delete campaign error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function processScheduledCampaigns()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');

        try {
            $result = $this->bulkSmsService->processDueCampaigns(50, 10, 3);
            $this->json([
                'success' => true,
                'message' => 'Scheduled campaign processor completed',
                'campaign_count' => $result['campaign_count'] ?? 0,
                'processed_count' => $result['processed_count'] ?? 0,
                'sent_count' => $result['sent_count'] ?? 0,
                'failed_count' => $result['failed_count'] ?? 0,
                'pending_count' => $result['pending_count'] ?? 0,
            ]);
        } catch (Throwable $e) {
            error_log('Process scheduled SMS campaigns error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function syncDeliveryStatuses()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $limit = isset($input['limit']) ? (int) $input['limit'] : 100;
            $result = $this->bulkSmsService->syncDeliveryStatuses($limit);
            $this->json([
                'success' => true,
                'message' => 'SMS delivery statuses synced',
                'checked_count' => $result['checked_count'] ?? 0,
                'updated_count' => $result['updated_count'] ?? 0,
                'delivered_count' => $result['delivered_count'] ?? 0,
                'undelivered_count' => $result['undelivered_count'] ?? 0,
                'failed_checks' => $result['failed_checks'] ?? 0,
            ]);
        } catch (Throwable $e) {
            error_log('SMS delivery sync error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Send quick SMS
     */
    public function quickSms()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $this->validateCsrf();

            $recipientType  = $this->sanitizeInput($_POST['recipient_type'] ?? '');
            $recipientGroup = $this->sanitizeInput($_POST['recipient_group'] ?? '');
            $recipientId    = (int) ($_POST['recipient_id'] ?? 0);
            $message        = $this->sanitizeInput($_POST['message'] ?? '');

            if (empty($message) || strlen($message) > 160) {
                throw new Exception('Message must be between 1 and 160 characters');
            }

            require_once __DIR__ . '/../services/SmsService.php';
            $smsService = new SmsService();

            // Resolve recipients based on recipient type. Keep member details so
            // supported placeholders can be personalized per recipient.
            $recipients = [];

            if ($recipientType === 'individual') {
                // Single member by member_id (recipient_id holds member id)
                $member = $this->memberModel->getMemberById($recipientId);
                if (!$member || empty($member['phone'])) {
                    throw new Exception('Member not found or has no phone number.');
                }
                $recipients[] = $this->quickSmsRecipientFromMember($member);

            } elseif ($recipientType === 'group') {
                // Group: active / inactive / pending
                $statusMap = [
                    'active'   => 'active',
                    'inactive' => 'inactive',
                    'pending'  => 'inactive', // pending members are stored as inactive+pending
                ];
                $sql = "SELECT DISTINCT u.id AS user_id, u.phone, u.first_name, u.last_name,
                               m.member_number, m.package, m.status, m.monthly_contribution
                        FROM members m
                        JOIN users u ON m.user_id = u.id
                        WHERE m.status = :status AND u.phone IS NOT NULL AND u.phone != ''";
                $status = $statusMap[$recipientGroup] ?? 'active';
                // For 'pending' group also include status='pending'
                if ($recipientGroup === 'pending') {
                    $sql = "SELECT DISTINCT u.id AS user_id, u.phone, u.first_name, u.last_name,
                                   m.member_number, m.package, m.status, m.monthly_contribution
                            FROM members m
                            JOIN users u ON m.user_id = u.id
                            WHERE m.status IN ('inactive','pending') AND u.phone IS NOT NULL AND u.phone != ''";
                    $rows = $this->db->fetchAll($sql);
                } else {
                    $rows = $this->db->fetchAll($sql, ['status' => $status]);
                }
                foreach ($rows as $row) {
                    $recipients[] = $this->quickSmsRecipientFromMember($row);
                }

            } elseif ($recipientType === 'all') {
                $sql = "SELECT DISTINCT u.id AS user_id, u.phone, u.first_name, u.last_name,
                               m.member_number, m.package, m.status, m.monthly_contribution
                        FROM members m
                        JOIN users u ON m.user_id = u.id
                        WHERE u.phone IS NOT NULL AND u.phone != ''";
                $rows = $this->db->fetchAll($sql);
                foreach ($rows as $row) {
                    $recipients[] = $this->quickSmsRecipientFromMember($row);
                }
            } else {
                throw new Exception('Please select a recipient type.');
            }

            if (empty($recipients)) {
                throw new Exception('No recipients found for the selected group.');
            }

            $validRecipients = [];
            foreach ($recipients as $recipient) {
                $formatted = $smsService->formatPhoneNumber($recipient['phone']);
                if (!$smsService->validatePhoneNumber($formatted)) {
                    continue;
                }
                $personalizedMessage = $this->personalizeQuickSmsMessage($message, $recipient);
                if (strlen($personalizedMessage) > 160) {
                    continue;
                }
                $recipient['phone'] = $formatted;
                $validRecipients[] = $recipient;
            }

            if (empty($validRecipients)) {
                throw new Exception('No valid recipients found for the selected message.');
            }

            $queueIds = $this->bulkSmsService->queueQuickSms($validRecipients, $message, (int)($_SESSION['user_id'] ?? 0));
            $result = $this->bulkSmsService->processQueueByIds($queueIds);
            $submitted = (int)($result['submitted_count'] ?? $result['sent_count'] ?? 0);
            $failed = (int)($result['failed_count'] ?? 0);
            $msg = "SMS submitted for {$submitted} recipient(s). Delivery status will update automatically.";
            if ($failed > 0) $msg .= " ({$failed} failed)";
            echo json_encode([
                'success' => true,
                'message' => $msg,
                'queued' => count($queueIds),
                'submitted' => $submitted,
                'failed' => $failed
            ]);
            exit();

        } catch (Throwable $e) {
            error_log('Quick SMS error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }

    private function quickSmsRecipientFromMember(array $member): array
    {
        $firstName = trim((string)($member['first_name'] ?? ''));
        $lastName = trim((string)($member['last_name'] ?? ''));

        return [
            'user_id' => $member['user_id'] ?? $member['id'] ?? null,
            'phone' => $member['phone'] ?? '',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'member_name' => trim($firstName . ' ' . $lastName),
            'member_number' => $member['member_number'] ?? '',
            'package' => $member['package'] ?? '',
            'status' => $member['status'] ?? '',
            'amount_due' => $member['monthly_contribution'] ?? '',
        ];
    }

    private function personalizeQuickSmsMessage(string $message, array $recipient): string
    {
        $replacements = [
            '{member_name}' => $recipient['member_name'] ?? '',
            '{first_name}' => $recipient['first_name'] ?? '',
            '{last_name}' => $recipient['last_name'] ?? '',
            '{member_number}' => $recipient['member_number'] ?? '',
            '{package}' => $recipient['package'] ?? '',
            '{status}' => $recipient['status'] ?? '',
            '{amount_due}' => $recipient['amount_due'] ?? '',
        ];

        return strtr($message, $replacements);
    }
    
    /**
     * Send scheduled campaign now (JSON response)
     */
    public function sendNow()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $campaignId = $input['campaign_id'] ?? 0;
            
            if (!$campaignId) {
                throw new Exception('Campaign ID is required');
            }
            
            // Update scheduled_at to null and send
            $this->bulkSmsService->updateScheduledAt($campaignId, null);
            $result = $this->bulkSmsService->sendCampaignUntilComplete($campaignId, 50, 10);
            
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'message' => 'Campaign submitted. Delivery confirmation will update after sync.',
                    'sent_count' => $result['sent_count'],
                    'failed_count' => $result['failed_count'] ?? 0,
                    'pending_count' => $result['pending_count'] ?? 0,
                    'processed_count' => $result['processed_count'] ?? 0
                ]);
            } else {
                throw new Exception($result['error'] ?? 'Failed to send campaign');
            }
            
        } catch (Exception $e) {
            error_log('Send now error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * View campaign details
     */
    public function viewCampaign($id)
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        $campaign = $this->bulkSmsService->getCampaignById($id);
        
        if (!$campaign) {
            $_SESSION['error'] = 'Campaign not found';
            $this->redirect('/admin/communications');
            return;
        }
        
        $recipientStatus = trim((string)($_GET['recipient_status'] ?? 'all'));
        $recipientSearch = trim((string)($_GET['recipient_search'] ?? ''));
        $recipientPage = max(1, (int)($_GET['recipient_page'] ?? 1));
        $recipientPerPage = 25;
        $allRecipients = $this->bulkSmsService->getCampaignRecipients($id);
        $filteredRecipients = array_values(array_filter($allRecipients, function ($recipient) use ($recipientStatus, $recipientSearch) {
            if ($recipientStatus !== '' && $recipientStatus !== 'all' && ($recipient['status'] ?? 'pending') !== $recipientStatus) {
                return false;
            }
            if ($recipientSearch !== '') {
                $haystack = strtolower(implode(' ', [
                    $recipient['first_name'] ?? '',
                    $recipient['last_name'] ?? '',
                    $recipient['member_number'] ?? '',
                    $recipient['recipient_value'] ?? '',
                    $recipient['phone'] ?? '',
                    $recipient['email'] ?? '',
                ]));
                if (strpos($haystack, strtolower($recipientSearch)) === false) {
                    return false;
                }
            }
            return true;
        }));
        $recipientTotal = count($filteredRecipients);
        $recipientTotalPages = max(1, (int)ceil($recipientTotal / $recipientPerPage));
        if ($recipientPage > $recipientTotalPages) {
            $recipientPage = $recipientTotalPages;
        }
        $recipients = array_slice($filteredRecipients, ($recipientPage - 1) * $recipientPerPage, $recipientPerPage);
        $stats = $this->bulkSmsService->getCampaignStats($id);

        $this->view('admin.campaign-details', [
            'title' => 'SMS Campaign Report - ' . $campaign['title'],
            'campaign' => $campaign,
            'recipients' => $recipients,
            'recipient_filters' => [
                'status' => $recipientStatus,
                'search' => $recipientSearch,
            ],
            'recipient_pagination' => [
                'current_page' => $recipientPage,
                'total_pages' => $recipientTotalPages,
                'total_items' => $recipientTotal,
                'per_page' => $recipientPerPage,
            ],
            'stats' => $stats,
            'channel' => 'sms',
            'back_url' => '/admin/sms-campaigns'
        ]);
    }

    public function downloadDeliveryReport($id)
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);

        $campaign = $this->bulkSmsService->getCampaignById($id);
        if (!$campaign) {
            http_response_code(404);
            echo 'Campaign not found';
            return;
        }

        $recipients = $this->bulkSmsService->getCampaignRecipients($id);
        $safeTitle = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string)($campaign['title'] ?? 'sms_campaign'));
        $filename = 'sms_delivery_report_' . $safeTitle . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Campaign ID',
            'Campaign Title',
            'Recipient Name',
            'Member Number',
            'Phone',
            'Status',
            'Delivery Method',
            'Provider Reference',
            'Provider Status',
            'Provider Cause',
            'Submitted At',
            'Delivered At',
            'DLR Checked At',
            'Error',
            'Provider Response',
        ]);

        foreach ($recipients as $recipient) {
            fputcsv($out, [
                $campaign['id'] ?? $id,
                $campaign['title'] ?? '',
                trim(($recipient['first_name'] ?? '') . ' ' . ($recipient['last_name'] ?? '')),
                $this->formatCsvIdentifier($recipient['member_number'] ?? ''),
                $this->formatCsvIdentifier($recipient['recipient_value'] ?? $recipient['phone'] ?? ''),
                $recipient['status'] ?? 'pending',
                $recipient['delivery_method'] ?? '',
                $recipient['provider_message_id'] ?? '',
                $recipient['provider_status'] ?? '',
                $recipient['provider_cause'] ?? '',
                $recipient['submitted_at'] ?? $recipient['sent_at'] ?? '',
                $recipient['delivered_at'] ?? '',
                $recipient['dlr_checked_at'] ?? '',
                $recipient['error_message'] ?? '',
                $recipient['provider_response'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    private function formatCsvIdentifier($value): string
    {
        $value = (string)$value;
        return $value === '' ? '' : '="' . str_replace('"', '""', $value) . '"';
    }

    public function resendPendingFailed($id)
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');

        try {
            $campaign = $this->bulkSmsService->getCampaignById($id);
            if (!$campaign) {
                throw new Exception('Campaign not found');
            }

            $resetCount = $this->bulkSmsService->resetRecipientsForResend((int)$id, ['pending', 'failed']);
            if ($resetCount < 1) {
                $this->json([
                    'success' => true,
                    'message' => 'No pending or failed recipients found for resend',
                    'reset_count' => 0,
                    'sent_count' => (int)($campaign['sent_count'] ?? 0),
                    'failed_count' => (int)($campaign['failed_count'] ?? 0),
                    'pending_count' => 0,
                ]);
                return;
            }

            $result = $this->bulkSmsService->sendCampaignUntilComplete((int)$id, 50, 10);
            if (empty($result['success'])) {
                throw new Exception($result['error'] ?? 'Failed to resend campaign recipients');
            }

            $this->json([
                'success' => true,
                'message' => 'Resend submitted for pending/failed recipients. Delivery confirmation will update after DLR sync.',
                'reset_count' => $resetCount,
                'sent_count' => $result['sent_count'] ?? 0,
                'failed_count' => $result['failed_count'] ?? 0,
                'pending_count' => $result['pending_count'] ?? 0,
                'processed_count' => $result['processed_count'] ?? 0,
            ]);
        } catch (Throwable $e) {
            error_log('Resend pending/failed SMS campaign error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reuseCampaign($id)
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');

        try {
            $newCampaignId = $this->bulkSmsService->reuseCampaignAsDraft((int)$id, (int)($_SESSION['user_id'] ?? 0));
            if (!$newCampaignId) {
                throw new Exception('Failed to reuse this campaign');
            }

            $this->json([
                'success' => true,
                'message' => 'Campaign copied as a new draft with refreshed recipients.',
                'campaign_id' => $newCampaignId,
                'redirect_url' => '/admin/communications/campaign/' . $newCampaignId,
            ]);
        } catch (Throwable $e) {
            error_log('Reuse SMS campaign error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function resendCampaignRecipient($id, $recipientId)
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');

        try {
            $result = $this->bulkSmsService->resendSingleRecipient((int)$id, (int)$recipientId);
            $this->json($result, !empty($result['success']) ? 200 : 400);
        } catch (Throwable $e) {
            error_log('Resend single campaign SMS error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Show SMS templates
     */
    public function templates()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        
        $templates = $this->bulkSmsService->getTemplates();
        
        // Store in session and redirect back
        $_SESSION['templates_view'] = $templates;
        $this->redirect('/admin/communications#campaigns');
    }
    
    /**
     * Edit campaign (stub for future implementation)
     */
    public function editCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');
        
        try {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $input = stripos($contentType, 'application/json') !== false
                ? (json_decode(file_get_contents('php://input'), true) ?: [])
                : $_POST;

            $campaignId = (int)($input['campaign_id'] ?? 0);
            $title = trim((string)($input['title'] ?? ''));
            $message = trim((string)($input['message'] ?? ''));
            $rawTargetAudience = trim((string)($input['target_audience'] ?? ''));
            $targetAudience = $this->normalizeTargetAudience($rawTargetAudience);
            $scheduledAt = !empty($input['scheduled_at']) ? $input['scheduled_at'] : null;

            if ($campaignId <= 0 || $title === '' || $message === '') {
                throw new Exception('Campaign ID, title, and message are required');
            }

            if ($rawTargetAudience === '') {
                // Never silently widen an edited campaign's audience to all_members;
                // reject the edit instead so filtered/payment-breakdown campaigns can't
                // be blasted to every member due to a missing/unselected UI value.
                throw new Exception('Target audience is required');
            }

            $customFilters = $this->extractCustomFilters($input);

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
                    WHERE id = ? AND message_type = 'sms' AND status IN ('draft', 'scheduled', 'paused')";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                $title,
                $message,
                $targetAudience,
                !empty($customFilters) ? json_encode($customFilters) : null,
                $scheduledAt,
                $scheduledAt,
                $campaignId
            ]);

            if ($stmt->rowCount() < 1) {
                $check = $this->db->getConnection()->prepare("SELECT COUNT(*) FROM bulk_messages WHERE id = ? AND message_type = 'sms' AND status IN ('draft', 'scheduled', 'paused')");
                $check->execute([$campaignId]);
                if ((int)$check->fetchColumn() > 0) {
                    $this->json(['success' => true, 'message' => 'SMS campaign updated successfully']);
                    return;
                }
                throw new Exception('Campaign not found or cannot be edited after sending has started');
            }

            $this->json(['success' => true, 'message' => 'SMS campaign updated successfully']);
        } catch (Throwable $e) {
            error_log('SMS edit campaign error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function extractCustomFilters(array $input): array
    {
        $filters = [];

        if (!empty($input['custom_filters']) && is_array($input['custom_filters'])) {
            $filters = $input['custom_filters'];
        }

        if (($input['target_audience'] ?? '') === 'inactive') {
            $filters['member_status'] = 'inactive';
        } elseif (($input['target_audience'] ?? '') === 'pending') {
            $filters['member_status'] = 'pending';
        }

        foreach (['filter_status' => 'member_status', 'filter_package' => 'package', 'filter_joined_after' => 'joined_after', 'filter_joined_before' => 'joined_before'] as $inputKey => $filterKey) {
            if (isset($input[$inputKey])) {
                $value = trim((string)$input[$inputKey]);
                if ($value !== '') {
                    $filters[$filterKey] = $value;
                } else {
                    unset($filters[$filterKey]);
                }
            }
        }

        return array_filter($filters, static fn($value) => $value !== null && $value !== '');
    }

    private function bulkMessagesHasColumn(string $column): bool
    {
        try {
            $stmt = $this->db->getConnection()->prepare('SHOW COLUMNS FROM bulk_messages LIKE ?');
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        } catch (Throwable $e) {
            error_log('bulk_messages column check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Pause campaign
     */
    public function pauseCampaign()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $campaignId = $input['campaign_id'] ?? 0;
            
            if (!$campaignId) {
                throw new Exception('Campaign ID is required');
            }
            
            $setClauses = ["status = 'paused'"];
            if ($this->bulkMessagesHasColumn('updated_at')) {
                $setClauses[] = 'updated_at = NOW()';
            }

            $sql = "UPDATE bulk_messages SET " . implode(', ', $setClauses) . "
                    WHERE id = ? AND status = 'sending'";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$campaignId]);
            
            if ($stmt->rowCount() > 0) {
                $this->json(['success' => true, 'message' => 'Campaign paused']);
            } else {
                throw new Exception('Campaign not found or cannot be paused');
            }
            
        } catch (Exception $e) {
            error_log('Pause campaign error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Reschedule campaign
     */
    public function reschedule()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $campaignId = $input['campaign_id'] ?? 0;
            $scheduledAt = $input['scheduled_at'] ?? null;
            
            if (!$campaignId || !$scheduledAt) {
                throw new Exception('Campaign ID and schedule time are required');
            }
            
            $setClauses = ['scheduled_at = ?', "status = 'scheduled'"];
            if ($this->bulkMessagesHasColumn('updated_at')) {
                $setClauses[] = 'updated_at = NOW()';
            }

            $sql = "UPDATE bulk_messages SET " . implode(', ', $setClauses) . "
                    WHERE id = ? AND status IN ('scheduled', 'draft')";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$scheduledAt, $campaignId]);
            
            if ($stmt->rowCount() > 0) {
                $this->json(['success' => true, 'message' => 'Campaign rescheduled']);
            } else {
                throw new Exception('Campaign not found or cannot be rescheduled');
            }
            
        } catch (Exception $e) {
            error_log('Reschedule error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Send individual queue item
     */
    public function sendQueueItem()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $itemId = $input['item_id'] ?? 0;
            
            if (!$itemId) {
                throw new Exception('Queue item ID is required');
            }
            
            // Get queue item
            $sql = "SELECT * FROM sms_queue WHERE id = ? AND status = 'pending'";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$itemId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$item) {
                throw new Exception('Queue item not found or already processed');
            }
            
            // Send SMS
            $result = $this->smsService->sendSms($item['phone_number'], $item['message']);
            
            if ($result && $result['success']) {
                $sql = "UPDATE sms_queue
                        SET status = 'submitted',
                            submitted_at = NOW(),
                            provider_message_id = ?,
                            provider_status = ?,
                            provider_cause = ?,
                            provider_response = ?
                        WHERE id = ?";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([
                    $result['provider_message_id'] ?? $result['data']['transactionId'] ?? null,
                    $result['provider_status'] ?? null,
                    $result['provider_cause'] ?? null,
                    json_encode($result),
                    $itemId
                ]);
                
                $this->json(['success' => true, 'message' => 'SMS submitted. Awaiting delivery confirmation.']);
            } else {
                $error = $result['error'] ?? 'Unknown error';
                $sql = "UPDATE sms_queue SET status = 'failed', error_message = ?, retry_count = retry_count + 1 WHERE id = ?";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([$error, $itemId]);
                
                throw new Exception('Failed to send SMS: ' . $error);
            }
            
        } catch (Exception $e) {
            error_log('Send queue item error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Retry failed queue item
     */
    public function retryQueueItem()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $itemId = $input['item_id'] ?? 0;
            
            if (!$itemId) {
                throw new Exception('Queue item ID is required');
            }
            
            // Reset status to pending
            $sql = "UPDATE sms_queue SET status = 'pending', error_message = NULL 
                    WHERE id = ? AND status = 'failed' AND retry_count < max_retries";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$itemId]);
            
            if ($stmt->rowCount() > 0) {
                $this->json(['success' => true, 'message' => 'Item queued for retry']);
            } else {
                throw new Exception('Cannot retry this item (max retries reached or not failed)');
            }
            
        } catch (Exception $e) {
            error_log('Retry queue item error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete queue item
     */
    public function deleteQueueItem()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $itemId = $input['item_id'] ?? 0;
            
            if (!$itemId) {
                throw new Exception('Queue item ID is required');
            }
            
            $sql = "DELETE FROM sms_queue WHERE id = ? AND status <> 'processing'";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$itemId]);
            
            if ($stmt->rowCount() > 0) {
                $this->json(['success' => true, 'message' => 'Queue item deleted']);
            } else {
                throw new Exception('Queue item not found or cannot be deleted');
            }
            
        } catch (Exception $e) {
            error_log('Delete queue item error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
