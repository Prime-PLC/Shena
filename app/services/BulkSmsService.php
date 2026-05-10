<?php
/**
 * Bulk SMS Service
 * Handles bulk SMS messaging campaigns with queue management
 * Now with automatic email fallback when SMS fails
 * 
 * @package Shena\Services
 */

require_once __DIR__ . '/../models/NotificationPreference.php';
require_once __DIR__ . '/SmsService.php';
require_once __DIR__ . '/NotificationService.php';

class BulkSmsService
{
    private $db;
    private $smsService;
    private $notificationService;
    private $notificationPreference;
    private $emailFallbackEnabled;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->smsService = new SmsService();
        $this->notificationService = new NotificationService();
        $this->notificationPreference = new NotificationPreference();
        
        // Load email fallback setting from database
        $this->emailFallbackEnabled = $this->getEmailFallbackSetting();
    }
    
    /**
     * Get email fallback setting from database
     */
    private function getEmailFallbackSetting()
    {
        try {
            $sql = "SELECT setting_value FROM settings WHERE setting_key = 'email_fallback_enabled'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (bool)$result['setting_value'] : true; // Default to enabled
        } catch (Exception $e) {
            return true; // Default to enabled if setting doesn't exist
        }
    }
    
    /**
     * Create a bulk SMS campaign
     * 
     * @param array $data Campaign data (title, message, target_audience, etc.)
     * @param int $createdBy User ID who created the campaign
     * @return int|false Campaign ID or false on failure
     */
    public function createCampaign($data, $createdBy)
    {
        $status = !empty($data['scheduled_at']) ? 'scheduled' : 'draft';
        $sql = "INSERT INTO bulk_messages (
                    title, message, message_type, target_audience, 
                    custom_filters, scheduled_at, created_by, status
                ) VALUES (?, ?, 'sms', ?, ?, ?, ?, ?)";
        
        $params = [
            $data['title'],
            $data['message'],
            $data['target_audience'],
            isset($data['custom_filters']) ? json_encode($data['custom_filters']) : null,
            $data['scheduled_at'] ?? null,
            $createdBy,
            $status
        ];
        
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Get recipients based on target audience
     * 
     * @param string $targetAudience Target audience type
     * @param array $customFilters Optional custom filters
     * @return array List of recipients with user_id, phone
     */
    public function getRecipients($targetAudience, $customFilters = [])
    {
        $sql = "SELECT DISTINCT u.id as user_id, u.phone, 
                       m.member_number as name, m.status
                FROM users u
                JOIN members m ON u.id = m.user_id
                JOIN notification_preferences np ON u.id = np.user_id
                WHERE np.sms_enabled = 1 
                AND u.phone IS NOT NULL 
                AND u.phone != ''";
        
        $params = [];
        
        switch ($targetAudience) {
            case 'active':
                $sql .= " AND m.status = 'active'";
                break;
                
            case 'grace_period':
                $sql .= " AND m.status = 'grace_period'";
                break;
                
            case 'defaulted':
                $sql .= " AND m.status = 'defaulted'";
                break;
                
            case 'custom':
                // Apply custom filters
                if (!empty($customFilters['package'])) {
                    $sql .= " AND m.package = ?";
                    $params[] = $customFilters['package'];
                }
                
                if (!empty($customFilters['status'])) {
                    $sql .= " AND m.status = ?";
                    $params[] = $customFilters['status'];
                }
                
                if (!empty($customFilters['county'])) {
                    $sql .= " AND m.county = ?";
                    $params[] = $customFilters['county'];
                }
                
                if (!empty($customFilters['joined_after'])) {
                    $sql .= " AND m.created_at >= ?";
                    $params[] = $customFilters['joined_after'];
                }
                
                if (!empty($customFilters['joined_before'])) {
                    $sql .= " AND m.created_at <= ?";
                    $params[] = $customFilters['joined_before'] . ' 23:59:59';
                }
                break;
                
            case 'all_members':
            default:
                // No additional filter - all members with SMS enabled
                break;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Queue recipients for a bulk message
     * 
     * @param int $bulkMessageId Bulk message ID
     * @param array $recipients List of recipients
     * @return bool Success status
     */
    public function queueRecipients($bulkMessageId, $recipients)
    {
        $sql = "INSERT INTO bulk_message_recipients (
                    bulk_message_id, user_id, recipient_type, 
                    recipient_value, status
                ) VALUES (?, ?, 'sms', ?, 'pending')";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($recipients as $recipient) {
            // Always format phone number before queueing
            $phone = $this->smsService->formatPhoneNumber($recipient['phone']);
            $stmt->execute([
                $bulkMessageId,
                $recipient['user_id'],
                $phone
            ]);
        }
        
        // Update total recipient count
        $updateSql = "UPDATE bulk_messages 
                      SET total_recipients = ? 
                      WHERE id = ?";
        $this->db->prepare($updateSql)->execute([count($recipients), $bulkMessageId]);
        
        return true;
    }
    
    /**
     * Send bulk SMS campaign
     * 
     * @param int $bulkMessageId Bulk message ID
     * @param int $batchSize Number of messages to send per batch (for rate limiting)
     * @return array Results with sent_count and failed_count
     */
    public function sendCampaign($bulkMessageId, $batchSize = 50)
    {
        // Get campaign details
        $campaign = $this->getCampaignById($bulkMessageId);
        
        if (!$campaign || $campaign['status'] === 'completed') {
            return ['success' => false, 'error' => 'Campaign not found or already completed'];
        }
        
        // Update status to sending
        $this->updateCampaignStatus($bulkMessageId, 'sending', true);
        
        // Get pending recipients
        $sql = "SELECT * FROM bulk_message_recipients 
                WHERE bulk_message_id = ? AND status = 'pending' 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bulkMessageId, $batchSize]);
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sentCount = 0;
        $failedCount = 0;
        $emailFallbackCount = 0;
        
        foreach ($recipients as $recipient) {
            try {
                // Check if user is not in quiet hours
                if ($this->notificationPreference->isInQuietHours($recipient['user_id'])) {
                    continue; // Skip for now, will retry later
                }
                
                // Get user email for potential fallback
                $userEmail = $this->getUserEmail($recipient['user_id']);
                
                // Try to send SMS, with email fallback if enabled
                if ($this->emailFallbackEnabled && $userEmail) {
                    // Use NotificationService with automatic fallback
                    $recipientData = [
                        'phone' => $recipient['recipient_value'],
                        'email' => $userEmail,
                        'name' => $this->getUserName($recipient['user_id'])
                    ];
                    
                    $result = $this->notificationService->send(
                        $recipientData,
                        $campaign['message'],
                        $campaign['title'], // Email subject
                        null, // Use default email body format
                        true // Enable fallback
                    );
                    
                    if ($result['success']) {
                        $deliveryMethod = $result['method']; // 'sms' or 'email'
                        $this->updateRecipientStatus(
                            $recipient['id'], 
                            'sent', 
                            null, 
                            $deliveryMethod,
                            $result['data']['transactionId'] ?? null,
                            $result
                        );
                        
                        if ($deliveryMethod === 'email') {
                            $emailFallbackCount++;
                        }
                        $sentCount++;
                    } else {
                        $this->updateRecipientStatus(
                            $recipient['id'], 
                            'failed', 
                            $result['error'] ?? 'Unknown error'
                        );
                        $failedCount++;
                    }
                    
                } else {
                    // Standard SMS only (no fallback)
                    $result = $this->smsService->sendSms(
                        $recipient['recipient_value'],
                        $campaign['message']
                    );
                    
                    if ($result['success']) {
                        $this->updateRecipientStatus(
                            $recipient['id'],
                            'sent',
                            null,
                            'sms',
                            $result['data']['transactionId'] ?? null,
                            $result
                        );
                        $sentCount++;
                    } else {
                        $this->updateRecipientStatus(
                            $recipient['id'], 
                            'failed', 
                            $result['error'] ?? 'Unknown error'
                        );
                        $failedCount++;
                    }
                }
                
                // Rate limiting - small delay between messages
                usleep(100000); // 100ms delay
                
            } catch (Exception $e) {
                $this->updateRecipientStatus($recipient['id'], 'failed', $e->getMessage());
                $failedCount++;
            }
        }
        
        // Update campaign counts
        $this->updateCampaignCounts($bulkMessageId, $sentCount, $failedCount);
        
        // Check if campaign is complete
        $pendingCount = $this->getPendingRecipientCount($bulkMessageId);
        if ($pendingCount === 0) {
            $this->updateCampaignStatus($bulkMessageId, 'completed', false, true);
        }
        
        return [
            'success' => true,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'email_fallback_count' => $emailFallbackCount,
            'pending_count' => $pendingCount
        ];
    }
    
    /**
     * Get campaign by ID
     * 
     * @param int $bulkMessageId Campaign ID
     * @return array|false Campaign data or false
     */
    public function getCampaignById($bulkMessageId)
    {
        $sql = "SELECT bm.*, u.email as created_by_name
                FROM bulk_messages bm
                JOIN users u ON bm.created_by = u.id
                WHERE bm.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bulkMessageId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all campaigns with optional filters
     * 
     * @param array $filters Optional filters (status, date_from, date_to)
     * @param int $limit Result limit
     * @param int $offset Result offset
     * @return array List of campaigns
     */
    public function getAllCampaigns($filters = [], $limit = 50, $offset = 0)
    {
        $sql = "SELECT bm.*, u.email as created_by_name,
                       ROUND((bm.sent_count / NULLIF(bm.total_recipients, 0) * 100), 2) as success_rate
                FROM bulk_messages bm
                JOIN users u ON bm.created_by = u.id
                WHERE bm.message_type IN ('sms', 'both')";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND bm.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND bm.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND bm.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $sql .= " ORDER BY bm.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update campaign status
     * 
     * @param int $bulkMessageId Campaign ID
     * @param string $status New status
     * @param bool $setStarted Set started_at timestamp
     * @param bool $setCompleted Set completed_at timestamp
     * @return bool Success status
     */
    private function updateCampaignStatus($bulkMessageId, $status, $setStarted = false, $setCompleted = false)
    {
        $sql = "UPDATE bulk_messages SET status = ?";
        $params = [$status];
        
        if ($setStarted) {
            $sql .= ", started_at = NOW()";
        }
        
        if ($setCompleted) {
            $sql .= ", completed_at = NOW()";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $bulkMessageId;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update recipient status
     * 
     * @param int $recipientId Recipient ID
     * @param string $status New status
     * @param string $errorMessage Optional error message
     * @return bool Success status
     */
    private function updateRecipientStatus($recipientId, $status, $errorMessage = null, $deliveryMethod = null, $providerMessageId = null, $providerResponse = null)
    {
        $sql = "UPDATE bulk_message_recipients 
                SET status = ?, sent_at = NOW(), error_message = ?, 
                    delivery_method = ?,
                    provider_message_id = ?,
                    provider_response = ?,
                    email_fallback_sent = IF(delivery_method = 'email', 1, 0),
                    email_sent_at = IF(delivery_method = 'email', NOW(), NULL)
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $encodedResponse = $providerResponse === null ? null : json_encode($providerResponse);
        return $stmt->execute([$status, $errorMessage, $deliveryMethod, $providerMessageId, $encodedResponse, $recipientId]);
    }
    
    /**
     * Update campaign sent and failed counts
     * 
     * @param int $bulkMessageId Campaign ID
     * @param int $sentCount Number sent in this batch
     * @param int $failedCount Number failed in this batch
     * @return bool Success status
     */
    private function updateCampaignCounts($bulkMessageId, $sentCount, $failedCount)
    {
        $sql = "UPDATE bulk_messages 
                SET sent_count = sent_count + ?, 
                    failed_count = failed_count + ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$sentCount, $failedCount, $bulkMessageId]);
    }
    
    /**
     * Get pending recipient count
     * 
     * @param int $bulkMessageId Campaign ID
     * @return int Pending count
     */
    private function getPendingRecipientCount($bulkMessageId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM bulk_message_recipients 
                WHERE bulk_message_id = ? AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bulkMessageId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'];
    }
    
    /**
     * Delete campaign and all recipients
     * 
     * @param int $bulkMessageId Campaign ID
     * @return bool Success status
     */
    public function deleteCampaign($bulkMessageId)
    {
        // Can only delete draft campaigns
        $campaign = $this->getCampaignById($bulkMessageId);
        if (!$campaign || $campaign['status'] !== 'draft') {
            return false;
        }
        
        // Delete recipients first (foreign key constraint)
        $sql = "DELETE FROM bulk_message_recipients WHERE bulk_message_id = ?";
        $this->db->prepare($sql)->execute([$bulkMessageId]);
        
        // Delete campaign
        $sql = "DELETE FROM bulk_messages WHERE id = ?";
        return $this->db->prepare($sql)->execute([$bulkMessageId]);
    }
    
    /**
     * Get campaign statistics
     * 
     * @param int $bulkMessageId Campaign ID
     * @return array Statistics
     */
    public function getCampaignStats($bulkMessageId)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM bulk_message_recipients
                WHERE bulk_message_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bulkMessageId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get active campaign count
     */
    public function getActiveCampaignCount()
    {
        $sql = "SELECT COUNT(*) as count FROM bulk_messages 
                WHERE status IN ('sending', 'scheduled')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get messages sent today
     */
    public function getSentCountToday()
    {
        $sql = "SELECT COUNT(*) as count FROM bulk_message_recipients 
                WHERE status = 'sent' AND DATE(sent_at) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    public function getTotalSentCount()
    {
        $sql = "SELECT COUNT(*) as count FROM bulk_message_recipients
                WHERE recipient_type = 'sms' AND status = 'sent'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    public function getFailedCount()
    {
        $sql = "SELECT COUNT(*) as count FROM bulk_message_recipients
                WHERE recipient_type = 'sms' AND status = 'failed'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get queue pending count
     */
    public function getQueuePendingCount()
    {
        $sql = "SELECT COUNT(*) as count FROM sms_queue WHERE status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get SMS credits balance
     */
    public function getSmsCredits()
    {
        $sql = "SELECT balance FROM sms_credits LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['balance'] ?? 0;
    }
    
    /**
     * Get queue items
     */
    public function getQueueItems($limit = 50)
    {
        $sql = "SELECT * FROM sms_queue ORDER BY priority DESC, created_at ASC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get SMS templates
     */
    public function getTemplates()
    {
        $sql = "SELECT * FROM sms_templates WHERE is_active = 1 ORDER BY category, name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cancel campaign
     */
    public function cancelCampaign($campaignId)
    {
        $sql = "UPDATE bulk_messages SET status = 'cancelled' WHERE id = ? 
                AND status IN ('draft', 'scheduled')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$campaignId]);
    }
    
    /**
     * Update scheduled_at
     */
    public function updateScheduledAt($campaignId, $scheduledAt)
    {
        $sql = "UPDATE bulk_messages SET scheduled_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$scheduledAt, $campaignId]);
    }
    
    /**
     * Get campaign recipients (updated to include email)
     */
    public function getCampaignRecipients($campaignId, $status = null)
    {
        $sql = "SELECT bmr.*, u.first_name, u.last_name, u.phone, u.email,
                       bmr.delivery_method, bmr.email_fallback_sent, bmr.email_sent_at
                FROM bulk_message_recipients bmr
                JOIN users u ON bmr.user_id = u.id
                WHERE bmr.bulk_message_id = ?";
        
        $params = [$campaignId];
        
        if ($status) {
            $sql .= " AND bmr.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY bmr.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user email
     */
    private function getUserEmail($userId)
    {
        $sql = "SELECT email FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['email'] : null;
    }
    
    /**
     * Get user full name
     */
    private function getUserName($userId)
    {
        $sql = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['full_name'] : 'Member';
    }
    
    /**
     * Process SMS queue
     */
    public function processQueue($batchSize = 100)
    {
        $sql = "SELECT * FROM sms_queue 
                WHERE status = 'pending' 
                AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                ORDER BY priority DESC, created_at ASC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$batchSize]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sentCount = 0;
        $failedCount = 0;
        
        foreach ($items as $item) {
            try {
                $result = $this->smsService->sendSms($item['phone_number'], $item['message']);
                
                if ($result['success']) {
                    $this->updateQueueStatus($item['id'], 'sent');
                    $sentCount++;
                } else {
                    $this->updateQueueStatus($item['id'], 'failed', $result['error'] ?? 'Unknown error');
                    $failedCount++;
                }
                
                usleep(100000); // 100ms delay between messages
                
            } catch (Exception $e) {
                $this->updateQueueStatus($item['id'], 'failed', $e->getMessage());
                $failedCount++;
            }
        }
        
        return [
            'sent_count' => $sentCount,
            'failed_count' => $failedCount
        ];
    }
    
    /**
     * Update queue item status
     */
    private function updateQueueStatus($queueId, $status, $errorMessage = null)
    {
        $sql = "UPDATE sms_queue SET status = ?, sent_at = NOW(), error_message = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $errorMessage, $queueId]);
    }
}
