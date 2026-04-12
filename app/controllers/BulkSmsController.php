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
        
        // For now, provide empty data until campaign tables are created
        $campaigns = [];
        $queue_items = [];
        $templates = [];
        
        // Get statistics from communications table
        $stats = [
            'active_campaigns' => 0,
            'sent_today' => $this->getSmsSentToday(),
            'queue_pending' => 0,
            'sms_credits' => 0,
            'total_sent' => $this->getTotalSmsSent(),
            'failed_count' => $this->getFailedSmsCount()
        ];
        
        $data = [
            'title' => 'SMS Campaigns - Shena Companion',
            'campaigns' => $campaigns,
            'queue_items' => $queue_items,
            'templates' => $templates,
            'stats' => $stats
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
            $message = "Sent: {$result['sent_count']}, Failed: {$result['failed_count']}, Pending: {$result['pending_count']}";
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
        
        $targetAudience = $_GET['target_audience'] ?? 'all_members';
        $customFilters = [];
        
        if ($targetAudience === 'custom') {
            if (!empty($_GET['filter_package'])) {
                $customFilters['package'] = $_GET['filter_package'];
            }
            if (!empty($_GET['filter_status'])) {
                $customFilters['status'] = $_GET['filter_status'];
            }
            if (!empty($_GET['filter_county'])) {
                $customFilters['county'] = $_GET['filter_county'];
            }
        }
        
        $recipients = $this->bulkSmsService->getRecipients($targetAudience, $customFilters);
        
        echo json_encode([
            'count' => count($recipients),
            'sample' => array_slice($recipients, 0, 10) // First 10 for preview
        ]);
        exit;
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
                    COUNT(CASE WHEN m.package = 'individual' THEN 1 END) as individual_count,
                    COUNT(CASE WHEN m.package = 'family' THEN 1 END) as family_count,
                    COUNT(CASE WHEN m.package = 'extended_family_1' THEN 1 END) as extended_family_1_count,
                    COUNT(CASE WHEN m.package = 'extended_family_2' THEN 1 END) as extended_family_2_count,
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
            $targetAudience = $_POST['target_audience'] ?? '';
            $action = $_POST['action'] ?? 'draft'; // draft or send
            $sendTime = $_POST['send_time'] ?? 'now';
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
                    'status' => $_POST['filter_status'] ?? null,
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
                $successMsg = 'Campaign created and sending started! (' . count($recipients) . ' recipients)';
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
            
            $result = $this->bulkSmsService->sendCampaign($campaignId);
            
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'message' => 'Campaign sending started',
                    'sent_count' => $result['sent_count'],
                    'failed_count' => $result['failed_count'],
                    'pending_count' => $result['pending_count']
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
            $result = $this->bulkSmsService->processQueue(100);
            
            $this->json([
                'success' => true,
                'message' => 'Queue processed',
                'sent_count' => $result['sent_count'],
                'failed_count' => $result['failed_count']
            ]);
            
        } catch (Exception $e) {
            error_log('Process queue error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Send quick SMS
     */
    public function quickSms()
    {
        $this->requireRole(['admin', 'super_admin', 'manager']);

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

            // Resolve phone number(s) based on recipient type
            $phones = [];

            if ($recipientType === 'individual') {
                // Single member by member_id (recipient_id holds member id)
                $member = $this->memberModel->findById($recipientId);
                if (!$member || empty($member['phone'])) {
                    throw new Exception('Member not found or has no phone number.');
                }
                $phones[] = $member['phone'];

            } elseif ($recipientType === 'group') {
                // Group: active / inactive / pending
                $statusMap = [
                    'active'   => 'active',
                    'inactive' => 'inactive',
                    'pending'  => 'inactive', // pending members are stored as inactive+pending
                ];
                $sql = "SELECT u.phone FROM members m
                        JOIN users u ON m.user_id = u.id
                        WHERE m.status = :status AND u.phone IS NOT NULL AND u.phone != ''";
                $status = $statusMap[$recipientGroup] ?? 'active';
                // For 'pending' group also include status='pending'
                if ($recipientGroup === 'pending') {
                    $sql = "SELECT u.phone FROM members m
                            JOIN users u ON m.user_id = u.id
                            WHERE m.status IN ('inactive','pending') AND u.phone IS NOT NULL AND u.phone != ''";
                    $rows = $this->db->fetchAll($sql);
                } else {
                    $rows = $this->db->fetchAll($sql, ['status' => $status]);
                }
                foreach ($rows as $row) {
                    $phones[] = $row['phone'];
                }

            } elseif ($recipientType === 'all') {
                $sql = "SELECT u.phone FROM members m
                        JOIN users u ON m.user_id = u.id
                        WHERE u.phone IS NOT NULL AND u.phone != ''";
                $rows = $this->db->fetchAll($sql);
                foreach ($rows as $row) {
                    $phones[] = $row['phone'];
                }
            } else {
                throw new Exception('Please select a recipient type.');
            }

            if (empty($phones)) {
                throw new Exception('No recipients found for the selected group.');
            }

            // Normalize phones and send
            $sent = 0;
            $failed = 0;
            $lastError = '';
            foreach ($phones as $rawPhone) {
                $formatted = $smsService->formatPhoneNumber($rawPhone);
                if (!$smsService->validatePhoneNumber($formatted)) {
                    $failed++;
                    continue;
                }
                $result = $smsService->sendSms($formatted, $message);
                if ($result['success']) {
                    $sent++;
                } else {
                    $failed++;
                    $lastError = $result['error'] ?? 'Send failed';
                }
            }

            if ($sent === 0) {
                throw new Exception($lastError ?: 'Failed to send SMS to any recipient.');
            }

            header('Content-Type: application/json');
            $msg = "SMS sent to {$sent} recipient(s)";
            if ($failed > 0) $msg .= " ({$failed} skipped)";
            echo json_encode(['success' => true, 'message' => $msg]);
            exit();

        } catch (Exception $e) {
            error_log('Quick SMS error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
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
            $result = $this->bulkSmsService->sendCampaign($campaignId);
            
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'message' => 'Campaign is being sent now',
                    'sent_count' => $result['sent_count']
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
        
        $recipients = $this->bulkSmsService->getCampaignRecipients($id);
        
        // Redirect back to communications with campaign details in session
        $_SESSION['campaign_view'] = [
            'campaign' => $campaign,
            'recipients' => $recipients
        ];
        
        $this->redirect('/admin/communications#campaigns');
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
        
        $input = json_decode(file_get_contents('php://input'), true);
        $campaignId = $input['campaign_id'] ?? 0;
        
        // TODO: Implement edit functionality
        $this->json([
            'success' => false,
            'message' => 'Edit feature coming soon. Please create a new campaign instead.'
        ]);
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
            
            // Update campaign status to paused
            $sql = "UPDATE bulk_messages SET status = 'paused', updated_at = NOW() 
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
            
            $sql = "UPDATE bulk_messages SET scheduled_at = ?, updated_at = NOW() 
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
                $sql = "UPDATE sms_queue SET status = 'sent', sent_at = NOW() WHERE id = ?";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([$itemId]);
                
                $this->json(['success' => true, 'message' => 'SMS sent successfully']);
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
            
            $sql = "DELETE FROM sms_queue WHERE id = ? AND status IN ('pending', 'failed')";
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
