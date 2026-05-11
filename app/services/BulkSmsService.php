<?php
/**
 * Bulk SMS Service
 * Handles bulk SMS messaging campaigns with queue management.
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
        $this->emailFallbackEnabled = $this->getEmailFallbackSetting();
    }

    private function getEmailFallbackSetting()
    {
        try {
            $sql = "SELECT setting_value FROM settings WHERE setting_key = 'email_fallback_enabled'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (bool) $result['setting_value'] : true;
        } catch (Exception $e) {
            return true;
        }
    }

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

    public function getRecipients($targetAudience, $customFilters = [])
    {
        [$sql, $params] = $this->buildRecipientQuery($targetAudience, $customFilters);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildRecipientQuery($targetAudience, array $customFilters = [])
    {
        $sql = "SELECT DISTINCT
                    u.id AS user_id,
                    u.phone,
                    u.email,
                    u.first_name,
                    u.last_name,
                    m.member_number,
                    m.package,
                    m.status,
                    COALESCE(m.monthly_contribution, 0) AS amount_due
                FROM users u
                INNER JOIN members m ON u.id = m.user_id
                LEFT JOIN notification_preferences np ON u.id = np.user_id
                WHERE COALESCE(np.sms_enabled, 1) = 1";

        $params = [];
        $targetAudience = $this->normalizeAudience($targetAudience);

        if ($targetAudience === 'active') {
            $sql .= " AND m.status = ?";
            $params[] = 'active';
        } elseif ($targetAudience === 'grace_period') {
            $sql .= " AND m.status = ?";
            $params[] = 'grace_period';
        } elseif ($targetAudience === 'defaulted') {
            $sql .= " AND m.status = ?";
            $params[] = 'defaulted';
        } elseif ($targetAudience === 'custom') {
            $status = $customFilters['member_status'] ?? $customFilters['status'] ?? null;
            if (!empty($status)) {
                $sql .= " AND m.status = ?";
                $params[] = $status;
            }

            if (!empty($customFilters['package'])) {
                $sql .= " AND m.package = ?";
                $params[] = $customFilters['package'];
            }

            if (!empty($customFilters['county'])) {
                $sql .= " AND m.address LIKE ?";
                $params[] = '%' . $customFilters['county'] . '%';
            }

            if (!empty($customFilters['joined_after'])) {
                $sql .= " AND m.created_at >= ?";
                $params[] = $customFilters['joined_after'];
            }

            if (!empty($customFilters['joined_before'])) {
                $sql .= " AND m.created_at <= ?";
                $params[] = $customFilters['joined_before'] . ' 23:59:59';
            }
        }

        $sql .= " ORDER BY u.first_name ASC, u.last_name ASC, m.member_number ASC";

        return [$sql, $params];
    }

    private function normalizeAudience($targetAudience)
    {
        $map = [
            'active_only' => 'active',
            'defaulters' => 'defaulted',
            'inactive' => 'custom',
            'pending' => 'custom',
        ];

        return $map[$targetAudience] ?? ($targetAudience ?: 'all_members');
    }

    public function queueRecipients($bulkMessageId, $recipients)
    {
        $sql = "INSERT INTO bulk_message_recipients (
                    bulk_message_id, user_id, recipient_type, recipient_value, status, error_message
                ) VALUES (?, ?, 'sms', ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $queued = 0;

        foreach ($recipients as $recipient) {
            $phone = $this->smsService->formatPhoneNumber($recipient['phone'] ?? '');
            $status = 'pending';
            $error = null;

            if (!$this->smsService->validatePhoneNumber($phone)) {
                $status = 'failed';
                $error = 'invalid_phone';
            }

            $stmt->execute([
                $bulkMessageId,
                $recipient['user_id'],
                $phone,
                $status,
                $error
            ]);
            $queued++;
        }

        $this->syncCampaignRecipientTotals($bulkMessageId, $queued);
        $this->recalculateCampaignCounts($bulkMessageId);

        return true;
    }

    public function sendCampaign($bulkMessageId, $batchSize = 50)
    {
        $campaign = $this->getCampaignById($bulkMessageId);

        if (!$campaign || $campaign['status'] === 'completed') {
            return ['success' => false, 'error' => 'Campaign not found or already completed'];
        }

        $this->updateCampaignStatus($bulkMessageId, 'sending', true);

        $sql = "SELECT bmr.*, u.first_name, u.last_name, u.email, u.phone,
                       m.member_number, m.package, m.status AS member_status,
                       COALESCE(m.monthly_contribution, 0) AS amount_due
                FROM bulk_message_recipients bmr
                INNER JOIN users u ON bmr.user_id = u.id
                INNER JOIN members m ON u.id = m.user_id
                WHERE bmr.bulk_message_id = ? AND bmr.status = 'pending'
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bulkMessageId, $batchSize]);
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $emailFallbackCount = 0;

        foreach ($recipients as $recipient) {
            try {
                if ($this->notificationPreference->isInQuietHours($recipient['user_id'])) {
                    continue;
                }

                $message = $this->replacePlaceholders($campaign['message'], $recipient);

                if (!$this->smsService->validatePhoneNumber($recipient['recipient_value'])) {
                    $this->updateRecipientStatus($recipient['id'], 'failed', 'invalid_phone', 'failed');
                    continue;
                }

                if ($this->emailFallbackEnabled && !empty($recipient['email'])) {
                    $recipientData = [
                        'phone' => $recipient['recipient_value'],
                        'email' => $recipient['email'],
                        'name' => $this->recipientName($recipient)
                    ];

                    $result = $this->notificationService->send(
                        $recipientData,
                        $message,
                        $campaign['title'],
                        null,
                        true
                    );

                    if (!empty($result['success'])) {
                        $deliveryMethod = $result['method'] ?? 'sms';
                        if ($deliveryMethod === 'email') {
                            $emailFallbackCount++;
                        }
                        $this->updateRecipientStatus(
                            $recipient['id'],
                            'sent',
                            null,
                            $deliveryMethod,
                            $result['data']['transactionId'] ?? null,
                            $result
                        );
                    } else {
                        $this->updateRecipientStatus(
                            $recipient['id'],
                            'failed',
                            $result['error'] ?? 'Unknown error',
                            'failed',
                            null,
                            $result
                        );
                    }
                } else {
                    $result = $this->smsService->sendSms($recipient['recipient_value'], $message);

                    if (!empty($result['success'])) {
                        $this->updateRecipientStatus(
                            $recipient['id'],
                            'sent',
                            null,
                            'sms',
                            $result['data']['transactionId'] ?? null,
                            $result
                        );
                    } else {
                        $this->updateRecipientStatus(
                            $recipient['id'],
                            'failed',
                            $result['error'] ?? 'Unknown error',
                            'failed',
                            null,
                            $result
                        );
                    }
                }

                usleep(100000);
            } catch (Throwable $e) {
                $this->updateRecipientStatus($recipient['id'], 'failed', $e->getMessage(), 'failed');
            }
        }

        $counts = $this->recalculateCampaignCounts($bulkMessageId);

        if ((int) $counts['pending_count'] === 0) {
            $finalStatus = ((int) $counts['sent_count'] > 0 || (int) $counts['failed_count'] > 0) ? 'completed' : 'failed';
            $this->updateCampaignStatus($bulkMessageId, $finalStatus, false, true);
        }

        return [
            'success' => true,
            'sent_count' => (int) $counts['sent_count'],
            'failed_count' => (int) $counts['failed_count'],
            'email_fallback_count' => $emailFallbackCount,
            'pending_count' => (int) $counts['pending_count']
        ];
    }

    public function getCampaignById($bulkMessageId)
    {
        $sql = "SELECT bm.*, u.email AS created_by_name,
                       COALESCE(stats.total_count, 0) AS total_recipients,
                       COALESCE(stats.sent_count, 0) AS sent_count,
                       COALESCE(stats.failed_count, 0) AS failed_count
                FROM bulk_messages bm
                JOIN users u ON bm.created_by = u.id
                LEFT JOIN (
                    SELECT bulk_message_id,
                           COUNT(*) AS total_count,
                           SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent_count,
                           SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_count
                    FROM bulk_message_recipients
                    GROUP BY bulk_message_id
                ) stats ON bm.id = stats.bulk_message_id
                WHERE bm.id = ? AND bm.message_type IN ('sms', 'both')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bulkMessageId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllCampaigns($filters = [], $limit = 50, $offset = 0)
    {
        $sql = "SELECT bm.*, u.email AS created_by_name,
                       COALESCE(stats.total_count, 0) AS total_recipients,
                       COALESCE(stats.sent_count, 0) AS sent_count,
                       COALESCE(stats.failed_count, 0) AS failed_count,
                       ROUND((COALESCE(stats.sent_count, 0) / NULLIF(COALESCE(stats.total_count, 0), 0) * 100), 2) AS success_rate
                FROM bulk_messages bm
                JOIN users u ON bm.created_by = u.id
                LEFT JOIN (
                    SELECT bulk_message_id,
                           COUNT(*) AS total_count,
                           SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent_count,
                           SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_count
                    FROM bulk_message_recipients
                    GROUP BY bulk_message_id
                ) stats ON bm.id = stats.bulk_message_id
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

    private function updateRecipientStatus($recipientId, $status, $errorMessage = null, $deliveryMethod = null, $providerMessageId = null, $providerResponse = null)
    {
        $sql = "UPDATE bulk_message_recipients
                SET status = ?,
                    sent_at = CASE WHEN ? = 'sent' THEN NOW() ELSE sent_at END,
                    error_message = ?,
                    delivery_method = ?,
                    provider_message_id = ?,
                    provider_response = ?,
                    email_fallback_sent = ?,
                    email_sent_at = CASE WHEN ? = 'email' THEN NOW() ELSE email_sent_at END
                WHERE id = ?";

        $encodedResponse = $providerResponse === null ? null : json_encode($providerResponse);
        $fallbackSent = $deliveryMethod === 'email' ? 1 : 0;
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $status,
            $status,
            $errorMessage,
            $deliveryMethod,
            $providerMessageId,
            $encodedResponse,
            $fallbackSent,
            $deliveryMethod,
            $recipientId
        ]);
    }

    private function syncCampaignRecipientTotals($bulkMessageId, $fallbackTotal = null)
    {
        $sql = "UPDATE bulk_messages
                SET total_recipients = COALESCE((
                    SELECT COUNT(*) FROM bulk_message_recipients WHERE bulk_message_id = ?
                ), ?)
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$bulkMessageId, $fallbackTotal ?? 0, $bulkMessageId]);
    }

    private function recalculateCampaignCounts($bulkMessageId)
    {
        $sql = "SELECT
                    COUNT(*) AS total_count,
                    SUM(CASE WHEN bmr.status = 'sent' THEN 1 ELSE 0 END) AS sent_count,
                    SUM(CASE WHEN bmr.status = 'failed' THEN 1 ELSE 0 END) AS failed_count,
                    SUM(CASE WHEN bmr.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                    SUM(CASE WHEN bmr.status = 'skipped' THEN 1 ELSE 0 END) AS skipped_count
                FROM bulk_message_recipients bmr
                WHERE bmr.bulk_message_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bulkMessageId]);
        $counts = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $updateSql = "UPDATE bulk_messages
                      SET total_recipients = ?, sent_count = ?, failed_count = ?
                      WHERE id = ?";
        $this->db->prepare($updateSql)->execute([
            (int) ($counts['total_count'] ?? 0),
            (int) ($counts['sent_count'] ?? 0),
            (int) ($counts['failed_count'] ?? 0),
            $bulkMessageId
        ]);

        return [
            'total_count' => (int) ($counts['total_count'] ?? 0),
            'sent_count' => (int) ($counts['sent_count'] ?? 0),
            'failed_count' => (int) ($counts['failed_count'] ?? 0),
            'pending_count' => (int) ($counts['pending_count'] ?? 0),
            'skipped_count' => (int) ($counts['skipped_count'] ?? 0),
        ];
    }

    public function deleteCampaign($bulkMessageId)
    {
        $campaign = $this->getCampaignById($bulkMessageId);
        if (!$campaign || $campaign['status'] !== 'draft') {
            return false;
        }

        $this->db->prepare("DELETE FROM bulk_message_recipients WHERE bulk_message_id = ?")->execute([$bulkMessageId]);
        return $this->db->prepare("DELETE FROM bulk_messages WHERE id = ?")->execute([$bulkMessageId]);
    }

    public function getCampaignStats($bulkMessageId)
    {
        $counts = $this->recalculateCampaignCounts($bulkMessageId);
        return [
            'total' => $counts['total_count'],
            'sent' => $counts['sent_count'],
            'failed' => $counts['failed_count'],
            'pending' => $counts['pending_count'],
            'skipped' => $counts['skipped_count'],
        ];
    }

    public function getActiveCampaignCount()
    {
        $sql = "SELECT COUNT(*) AS count FROM bulk_messages
                WHERE message_type IN ('sms', 'both') AND status IN ('sending', 'scheduled')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    public function getSentCountToday()
    {
        $sql = "SELECT COUNT(*) AS count
                FROM bulk_message_recipients
                WHERE recipient_type = 'sms' AND status = 'sent' AND DATE(sent_at) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    public function getTotalSentCount()
    {
        $sql = "SELECT COUNT(*) AS count FROM bulk_message_recipients
                WHERE recipient_type = 'sms' AND status = 'sent'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    public function getFailedCount()
    {
        $sql = "SELECT COUNT(*) AS count FROM bulk_message_recipients
                WHERE recipient_type = 'sms' AND status = 'failed'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    public function getQueuePendingCount()
    {
        $sql = "SELECT COUNT(*) AS count FROM sms_queue WHERE status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    public function getSmsCredits()
    {
        $sql = "SELECT balance FROM sms_credits LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['balance'] ?? 0;
    }

    public function getQueueItems($limit = 50)
    {
        $sql = "SELECT * FROM sms_queue ORDER BY priority DESC, created_at ASC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTemplates()
    {
        $sql = "SELECT * FROM sms_templates WHERE is_active = 1 ORDER BY category, name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelCampaign($campaignId)
    {
        $sql = "UPDATE bulk_messages SET status = 'cancelled' WHERE id = ?
                AND status IN ('draft', 'scheduled', 'paused')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$campaignId]);
    }

    public function updateScheduledAt($campaignId, $scheduledAt)
    {
        $sql = "UPDATE bulk_messages SET scheduled_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$scheduledAt, $campaignId]);
    }

    public function getCampaignRecipients($campaignId, $status = null)
    {
        $sql = "SELECT bmr.*, u.first_name, u.last_name, u.phone, u.email,
                       m.member_number, m.package, m.status AS member_status,
                       bmr.delivery_method, bmr.email_fallback_sent, bmr.email_sent_at,
                       bmr.provider_message_id, bmr.provider_response
                FROM bulk_message_recipients bmr
                JOIN users u ON bmr.user_id = u.id
                JOIN members m ON u.id = m.user_id
                WHERE bmr.bulk_message_id = ?";

        $params = [$campaignId];

        if ($status) {
            $sql .= " AND bmr.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY bmr.sent_at DESC, bmr.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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

                if (!empty($result['success'])) {
                    $this->updateQueueStatus($item['id'], 'sent');
                    $sentCount++;
                } else {
                    $this->updateQueueStatus($item['id'], 'failed', $result['error'] ?? 'Unknown error');
                    $failedCount++;
                }

                usleep(100000);
            } catch (Throwable $e) {
                $this->updateQueueStatus($item['id'], 'failed', $e->getMessage());
                $failedCount++;
            }
        }

        return [
            'sent_count' => $sentCount,
            'failed_count' => $failedCount
        ];
    }

    private function updateQueueStatus($queueId, $status, $errorMessage = null)
    {
        $sql = "UPDATE sms_queue SET status = ?, sent_at = CASE WHEN ? = 'sent' THEN NOW() ELSE sent_at END, error_message = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $status, $errorMessage, $queueId]);
    }

    private function replacePlaceholders($message, array $recipient)
    {
        $name = $this->recipientName($recipient);
        $data = [
            'member_name' => $name,
            'name' => $name,
            'first_name' => $recipient['first_name'] ?? '',
            'last_name' => $recipient['last_name'] ?? '',
            'member_number' => $recipient['member_number'] ?? '',
            'phone' => $recipient['phone'] ?? $recipient['recipient_value'] ?? '',
            'email' => $recipient['email'] ?? '',
            'package' => $recipient['package'] ?? '',
            'status' => $recipient['member_status'] ?? $recipient['status'] ?? '',
            'amount_due' => number_format((float) ($recipient['amount_due'] ?? 0), 2),
        ];

        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', (string) $value, $message);
        }

        return $message;
    }

    private function recipientName(array $recipient)
    {
        $name = trim(($recipient['first_name'] ?? '') . ' ' . ($recipient['last_name'] ?? ''));
        return $name !== '' ? $name : 'Member';
    }
}
