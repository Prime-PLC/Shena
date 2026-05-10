<?php
/**
 * Bulk Email Service
 * Handles bulk email campaign creation, scheduling, and sending
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/../models/Member.php';

class BulkEmailService
{
    private $db;
    private $emailService;
    private $memberModel;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->emailService = new EmailService();
        $this->memberModel = new Member();
    }
    
    /**
     * Get all campaigns with optional filters
     */
    public function getAllCampaigns($filters = [])
    {
        $sql = "SELECT * FROM bulk_messages WHERE message_type = 'email'";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get single campaign by ID
     */
    public function getCampaign($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM bulk_messages WHERE id = ? AND message_type = 'email'");
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new email campaign
     */
    public function createCampaign($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO bulk_messages (
                title, message, message_type, target_audience, 
                custom_filters, scheduled_at, total_recipients, created_by, status
            ) VALUES (?, ?, 'email', ?, ?, ?, ?, ?, ?)
        ");
        
        $status = !empty($data['scheduled_at']) ? 'scheduled' : 'draft';
        $customFilters = $data['custom_filters'] ?? [];
        if (!empty($data['subject'])) {
            $customFilters['email_subject'] = $data['subject'];
        }
        
        $stmt->execute([
            $data['title'],
            $data['message'],
            $data['target_audience'],
            !empty($customFilters) ? json_encode($customFilters) : null,
            $data['scheduled_at'],
            $data['total_recipients'],
            $data['created_by'],
            $status
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get recipients based on target audience
     */
    public function getRecipients($targetAudience, $additionalFilters = [])
    {
        $sql = "SELECT u.id, u.email, u.first_name, u.last_name, m.member_number
                FROM users u
                INNER JOIN members m ON u.id = m.user_id
                WHERE u.email IS NOT NULL AND u.email != ''";
        
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
            case 'new_members':
                $sql .= " AND m.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case 'custom':
                if (!empty($additionalFilters['member_status'])) {
                    $sql .= " AND m.status = " . $this->db->quote($additionalFilters['member_status']);
                }
                if (!empty($additionalFilters['package'])) {
                    $sql .= " AND m.package = " . $this->db->quote($additionalFilters['package']);
                }
                if (!empty($additionalFilters['joined_after'])) {
                    $sql .= " AND m.created_at >= " . $this->db->quote($additionalFilters['joined_after']);
                }
                if (!empty($additionalFilters['joined_before'])) {
                    $sql .= " AND m.created_at <= " . $this->db->quote($additionalFilters['joined_before'] . ' 23:59:59');
                }
                break;
            case 'all_members':
            default:
                // No additional filter
                break;
        }
        
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Add recipients to campaign
     */
    public function addRecipients($campaignId, $recipients)
    {
        $stmt = $this->db->prepare("
            INSERT INTO bulk_message_recipients (
                bulk_message_id, user_id, recipient_type, recipient_value, status
            ) VALUES (?, ?, 'email', ?, 'pending')
        ");
        
        foreach ($recipients as $recipient) {
            $stmt->execute([
                $campaignId,
                $recipient['id'],
                $recipient['email']
            ]);
        }
        
        return true;
    }
    
    /**
     * Send campaign emails
     */
    public function sendCampaign($campaignId)
    {
        // Update campaign status to sending
        $this->updateCampaignStatus($campaignId, 'sending', ['started_at' => date('Y-m-d H:i:s')]);
        
        // Get pending recipients
        $stmt = $this->db->prepare("
            SELECT bmr.*, u.first_name, u.last_name, m.member_number, bm.message as email_body, bm.title as email_subject, bm.custom_filters
            FROM bulk_message_recipients bmr
            INNER JOIN users u ON bmr.user_id = u.id
            INNER JOIN members m ON u.id = m.user_id
            INNER JOIN bulk_messages bm ON bmr.bulk_message_id = bm.id
            WHERE bmr.bulk_message_id = ? AND bmr.status = 'pending'
        ");
        $stmt->execute([$campaignId]);
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sent = 0;
        $failed = 0;
        
        foreach ($recipients as $recipient) {
            try {
                // Replace placeholders in email body
                $body = $this->replacePlaceholders($recipient['email_body'], [
                    'name' => $recipient['first_name'] . ' ' . $recipient['last_name'],
                    'first_name' => $recipient['first_name'],
                    'last_name' => $recipient['last_name'],
                    'member_number' => $recipient['member_number'],
                    'email' => $recipient['recipient_value']
                ]);
                
                $campaignMeta = !empty($recipient['custom_filters']) ? json_decode($recipient['custom_filters'], true) : [];
                $subject = $campaignMeta['email_subject'] ?? $recipient['email_subject'];

                $result = $this->emailService->sendEmail(
                    $recipient['recipient_value'],
                    $subject,
                    $body,
                    true // isHtml = true
                );
                
                if ($result) {
                    $this->updateRecipientStatus($recipient['id'], 'sent');
                    $sent++;
                } else {
                    $this->updateRecipientStatus($recipient['id'], 'failed', 'Email send failed');
                    $failed++;
                }
            } catch (Exception $e) {
                error_log('Bulk email error: ' . $e->getMessage());
                $this->updateRecipientStatus($recipient['id'], 'failed', $e->getMessage());
                $failed++;
            }
            
            // Small delay to avoid overwhelming SMTP server
            usleep(100000); // 0.1 second
        }
        
        // Update campaign statistics
        $this->updateCampaignStats($campaignId, $sent, $failed);
        
        // Update campaign status to completed
        $this->updateCampaignStatus($campaignId, 'completed', ['completed_at' => date('Y-m-d H:i:s')]);
        
        return [
            'success' => true,
            'sent' => $sent,
            'failed' => $failed
        ];
    }
    
    /**
     * Replace placeholders in message
     */
    private function replacePlaceholders($message, $data)
    {
        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Update recipient status
     */
    private function updateRecipientStatus($recipientId, $status, $errorMessage = null)
    {
        $stmt = $this->db->prepare("
            UPDATE bulk_message_recipients 
            SET status = ?, error_message = ?, sent_at = ?
            WHERE id = ?
        ");
        
        $sentAt = $status === 'sent' ? date('Y-m-d H:i:s') : null;
        
        $stmt->execute([$status, $errorMessage, $sentAt, $recipientId]);
    }
    
    /**
     * Update campaign statistics
     */
    private function updateCampaignStats($campaignId, $sent, $failed)
    {
        $stmt = $this->db->prepare("
            UPDATE bulk_messages 
            SET sent_count = sent_count + ?, failed_count = failed_count + ?
            WHERE id = ?
        ");
        
        $stmt->execute([$sent, $failed, $campaignId]);
    }
    
    /**
     * Update campaign status
     */
    private function updateCampaignStatus($campaignId, $status, $additionalData = [])
    {
        $fields = ['status' => $status];
        $fields = array_merge($fields, $additionalData);
        
        $setClause = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
        $values = array_values($fields);
        $values[] = $campaignId;
        
        $stmt = $this->db->prepare("UPDATE bulk_messages SET $setClause WHERE id = ?");
        $stmt->execute($values);
    }
    
    /**
     * Cancel a campaign
     */
    public function cancelCampaign($campaignId)
    {
        $this->updateCampaignStatus($campaignId, 'cancelled');
        
        // Mark all pending recipients as skipped
        $stmt = $this->db->prepare("
            UPDATE bulk_message_recipients 
            SET status = 'skipped' 
            WHERE bulk_message_id = ? AND status = 'pending'
        ");
        
        return $stmt->execute([$campaignId]);
    }
    
    /**
     * Pause a campaign
     */
    public function pauseCampaign($campaignId)
    {
        return $this->updateCampaignStatus($campaignId, 'draft');
    }
    
    /**
     * Reschedule a campaign
     */
    public function rescheduleCampaign($campaignId, $scheduledAt)
    {
        $stmt = $this->db->prepare("
            UPDATE bulk_messages 
            SET scheduled_at = ?, status = 'scheduled'
            WHERE id = ?
        ");
        
        return $stmt->execute([$scheduledAt, $campaignId]);
    }
    
    /**
     * Get campaign recipients
     */
    public function getCampaignRecipients($campaignId)
    {
        $stmt = $this->db->prepare("
            SELECT bmr.*, u.first_name, u.last_name, m.member_number
            FROM bulk_message_recipients bmr
            INNER JOIN users u ON bmr.user_id = u.id
            INNER JOIN members m ON u.id = m.user_id
            WHERE bmr.bulk_message_id = ?
            ORDER BY bmr.sent_at DESC, bmr.id DESC
        ");
        $stmt->execute([$campaignId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCampaignStats($campaignId)
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END) as skipped
            FROM bulk_message_recipients
            WHERE bulk_message_id = ?
        ");
        $stmt->execute([$campaignId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retry failed recipients
     */
    public function retryFailedRecipients($campaignId)
    {
        // Reset failed recipients to pending
        $stmt = $this->db->prepare("
            UPDATE bulk_message_recipients 
            SET status = 'pending', error_message = NULL
            WHERE bulk_message_id = ? AND status = 'failed'
        ");
        $stmt->execute([$campaignId]);
        
        $retried = $stmt->rowCount();
        
        // Resend campaign
        $result = $this->sendCampaign($campaignId);
        $result['retried'] = $retried;
        
        return $result;
    }
    
    /**
     * Get email templates
     */
    public function getTemplates()
    {
        $stmt = $this->db->query("
            SELECT * FROM sms_templates 
            WHERE is_active = 1 
            ORDER BY category, name
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get statistics
     */
    public function getActiveCampaignCount()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) as count FROM bulk_messages 
            WHERE message_type = 'email' AND status IN ('sending', 'scheduled')
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] ?? 0;
    }
    
    public function getSentCountToday()
    {
        $stmt = $this->db->query("
            SELECT SUM(sent_count) as total FROM bulk_messages 
            WHERE message_type = 'email' AND DATE(started_at) = CURDATE()
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    public function getTotalSent()
    {
        $stmt = $this->db->query("
            SELECT SUM(sent_count) as total FROM bulk_messages 
            WHERE message_type = 'email'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    public function getFailedCount()
    {
        $stmt = $this->db->query("
            SELECT SUM(failed_count) as total FROM bulk_messages 
            WHERE message_type = 'email'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
}
