<?php
/**
 * Bulk Email Service
 * Handles bulk email campaign creation, scheduling, sending, and delivery stats.
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

    public function getAllCampaigns($filters = [])
    {
        $sql = "SELECT bm.*,
                       COALESCE(stats.total_count, 0) AS total_recipients,
                       COALESCE(stats.sent_count, 0) AS sent_count,
                       COALESCE(stats.failed_count, 0) AS failed_count
                FROM bulk_messages bm
                LEFT JOIN (
                    SELECT bulk_message_id,
                           COUNT(*) AS total_count,
                           SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent_count,
                           SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_count
                    FROM bulk_message_recipients
                    GROUP BY bulk_message_id
                ) stats ON bm.id = stats.bulk_message_id
                WHERE bm.message_type = 'email'";
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

        $sql .= " ORDER BY bm.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCampaign($id)
    {
        $sql = "SELECT bm.*,
                       COALESCE(stats.total_count, 0) AS total_recipients,
                       COALESCE(stats.sent_count, 0) AS sent_count,
                       COALESCE(stats.failed_count, 0) AS failed_count
                FROM bulk_messages bm
                LEFT JOIN (
                    SELECT bulk_message_id,
                           COUNT(*) AS total_count,
                           SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent_count,
                           SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_count
                    FROM bulk_message_recipients
                    GROUP BY bulk_message_id
                ) stats ON bm.id = stats.bulk_message_id
                WHERE bm.id = ? AND bm.message_type = 'email'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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
            $data['total_recipients'] ?? 0,
            $data['created_by'],
            $status
        ]);

        return $this->db->lastInsertId();
    }

    public function getRecipients($targetAudience, $additionalFilters = [])
    {
        [$sql, $params] = $this->buildRecipientQuery($targetAudience, $additionalFilters);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildRecipientQuery($targetAudience, array $additionalFilters = [])
    {
        $sql = "SELECT DISTINCT
                    u.id,
                    u.email,
                    u.phone,
                    u.first_name,
                    u.last_name,
                    m.member_number,
                    m.package,
                    m.status,
                    COALESCE(m.monthly_contribution, 0) AS amount_due
                FROM users u
                INNER JOIN members m ON u.id = m.user_id
                WHERE 1 = 1";
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
        } elseif ($targetAudience === 'new_members') {
            $sql .= " AND m.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        } elseif ($targetAudience === 'custom') {
            if (!empty($additionalFilters['member_status'])) {
                $sql .= " AND m.status = ?";
                $params[] = $additionalFilters['member_status'];
            }
            if (!empty($additionalFilters['status'])) {
                $sql .= " AND m.status = ?";
                $params[] = $additionalFilters['status'];
            }
            if (!empty($additionalFilters['package'])) {
                $sql .= " AND m.package = ?";
                $params[] = $additionalFilters['package'];
            }
            if (!empty($additionalFilters['joined_after'])) {
                $sql .= " AND m.created_at >= ?";
                $params[] = $additionalFilters['joined_after'];
            }
            if (!empty($additionalFilters['joined_before'])) {
                $sql .= " AND m.created_at <= ?";
                $params[] = $additionalFilters['joined_before'] . ' 23:59:59';
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

    public function addRecipients($campaignId, $recipients)
    {
        $stmt = $this->db->prepare("
            INSERT INTO bulk_message_recipients (
                bulk_message_id, user_id, recipient_type, recipient_value, status, error_message
            ) VALUES (?, ?, 'email', ?, ?, ?)
        ");

        foreach ($recipients as $recipient) {
            $email = trim((string) ($recipient['email'] ?? ''));
            $status = 'pending';
            $error = null;

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $status = 'failed';
                $error = 'invalid_email';
            }

            $stmt->execute([
                $campaignId,
                $recipient['id'],
                $email,
                $status,
                $error
            ]);
        }

        $this->recalculateCampaignCounts($campaignId);
        return true;
    }

    public function sendCampaign($campaignId)
    {
        $this->updateCampaignStatus($campaignId, 'sending', ['started_at' => date('Y-m-d H:i:s')]);

        $stmt = $this->db->prepare("
            SELECT bmr.*, u.first_name, u.last_name, u.phone,
                   m.member_number, m.package, m.status AS member_status,
                   COALESCE(m.monthly_contribution, 0) AS amount_due,
                   bm.message AS email_body, bm.title AS email_subject, bm.custom_filters
            FROM bulk_message_recipients bmr
            INNER JOIN users u ON bmr.user_id = u.id
            INNER JOIN members m ON u.id = m.user_id
            INNER JOIN bulk_messages bm ON bmr.bulk_message_id = bm.id
            WHERE bmr.bulk_message_id = ? AND bmr.status = 'pending'
        ");
        $stmt->execute([$campaignId]);
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($recipients as $recipient) {
            try {
                $email = trim((string) ($recipient['recipient_value'] ?? ''));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->updateRecipientStatus($recipient['id'], 'failed', 'invalid_email', null, [
                        'error' => 'invalid_email',
                        'recipient' => $email
                    ]);
                    continue;
                }

                $body = $this->replacePlaceholders($recipient['email_body'], $recipient);
                $campaignMeta = !empty($recipient['custom_filters']) ? json_decode($recipient['custom_filters'], true) : [];
                $subject = $campaignMeta['email_subject'] ?? $recipient['email_subject'];

                $result = $this->emailService->sendEmail($email, $subject, $body, true);

                if ($result) {
                    $this->updateRecipientStatus($recipient['id'], 'sent', null, null, [
                        'success' => true,
                        'method' => 'email'
                    ]);
                } else {
                    $this->updateRecipientStatus($recipient['id'], 'failed', 'Email send failed', null, [
                        'success' => false,
                        'error' => 'Email send failed'
                    ]);
                }
            } catch (Throwable $e) {
                error_log('Bulk email error: ' . $e->getMessage());
                $this->updateRecipientStatus($recipient['id'], 'failed', $e->getMessage(), null, [
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }

            usleep(100000);
        }

        $counts = $this->recalculateCampaignCounts($campaignId);
        if ((int) $counts['pending_count'] === 0) {
            $finalStatus = ((int) $counts['sent_count'] > 0 || (int) $counts['failed_count'] > 0) ? 'completed' : 'failed';
            $this->updateCampaignStatus($campaignId, $finalStatus, ['completed_at' => date('Y-m-d H:i:s')]);
        }

        return [
            'success' => true,
            'sent' => (int) $counts['sent_count'],
            'failed' => (int) $counts['failed_count']
        ];
    }

    private function replacePlaceholders($message, array $recipient)
    {
        $name = trim(($recipient['first_name'] ?? '') . ' ' . ($recipient['last_name'] ?? ''));
        if ($name === '') {
            $name = 'Member';
        }

        $data = [
            'member_name' => $name,
            'name' => $name,
            'first_name' => $recipient['first_name'] ?? '',
            'last_name' => $recipient['last_name'] ?? '',
            'member_number' => $recipient['member_number'] ?? '',
            'phone' => $recipient['phone'] ?? '',
            'email' => $recipient['recipient_value'] ?? '',
            'package' => $recipient['package'] ?? '',
            'status' => $recipient['member_status'] ?? $recipient['status'] ?? '',
            'amount_due' => number_format((float) ($recipient['amount_due'] ?? 0), 2),
        ];

        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', (string) $value, $message);
        }

        return $message;
    }

    private function updateRecipientStatus($recipientId, $status, $errorMessage = null, $providerMessageId = null, $providerResponse = null)
    {
        $stmt = $this->db->prepare("
            UPDATE bulk_message_recipients
            SET status = ?,
                error_message = ?,
                sent_at = CASE WHEN ? = 'sent' THEN NOW() ELSE sent_at END,
                delivery_method = ?,
                provider_message_id = ?,
                provider_response = ?
            WHERE id = ?
        ");

        $encodedResponse = $providerResponse === null ? null : json_encode($providerResponse);
        $stmt->execute([
            $status,
            $errorMessage,
            $status,
            $status === 'sent' ? 'email' : 'failed',
            $providerMessageId,
            $encodedResponse,
            $recipientId
        ]);
    }

    private function recalculateCampaignCounts($campaignId)
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS total_count,
                SUM(CASE WHEN bmr.status = 'sent' THEN 1 ELSE 0 END) AS sent_count,
                SUM(CASE WHEN bmr.status = 'failed' THEN 1 ELSE 0 END) AS failed_count,
                SUM(CASE WHEN bmr.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN bmr.status = 'skipped' THEN 1 ELSE 0 END) AS skipped_count
            FROM bulk_message_recipients bmr
            WHERE bmr.bulk_message_id = ?
        ");
        $stmt->execute([$campaignId]);
        $counts = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $update = $this->db->prepare("
            UPDATE bulk_messages
            SET total_recipients = ?, sent_count = ?, failed_count = ?
            WHERE id = ?
        ");
        $update->execute([
            (int) ($counts['total_count'] ?? 0),
            (int) ($counts['sent_count'] ?? 0),
            (int) ($counts['failed_count'] ?? 0),
            $campaignId
        ]);

        return [
            'total_count' => (int) ($counts['total_count'] ?? 0),
            'sent_count' => (int) ($counts['sent_count'] ?? 0),
            'failed_count' => (int) ($counts['failed_count'] ?? 0),
            'pending_count' => (int) ($counts['pending_count'] ?? 0),
            'skipped_count' => (int) ($counts['skipped_count'] ?? 0),
        ];
    }

    private function updateCampaignStatus($campaignId, $status, $additionalData = [])
    {
        $fields = ['status' => $status];
        $fields = array_merge($fields, $additionalData);

        $setClause = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
        $values = array_values($fields);
        $values[] = $campaignId;

        $stmt = $this->db->prepare("UPDATE bulk_messages SET $setClause WHERE id = ?");
        return $stmt->execute($values);
    }

    public function cancelCampaign($campaignId)
    {
        $this->updateCampaignStatus($campaignId, 'cancelled');

        $stmt = $this->db->prepare("
            UPDATE bulk_message_recipients
            SET status = 'skipped'
            WHERE bulk_message_id = ? AND status = 'pending'
        ");

        $result = $stmt->execute([$campaignId]);
        $this->recalculateCampaignCounts($campaignId);

        return $result;
    }

    public function pauseCampaign($campaignId)
    {
        return $this->updateCampaignStatus($campaignId, 'paused');
    }

    public function rescheduleCampaign($campaignId, $scheduledAt)
    {
        $stmt = $this->db->prepare("
            UPDATE bulk_messages
            SET scheduled_at = ?, status = 'scheduled'
            WHERE id = ?
        ");

        return $stmt->execute([$scheduledAt, $campaignId]);
    }

    public function getCampaignRecipients($campaignId)
    {
        $stmt = $this->db->prepare("
            SELECT bmr.*, u.first_name, u.last_name, u.phone, u.email,
                   m.member_number, m.package, m.status AS member_status,
                   bmr.provider_message_id, bmr.provider_response
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
        $counts = $this->recalculateCampaignCounts($campaignId);
        return [
            'total' => $counts['total_count'],
            'sent' => $counts['sent_count'],
            'failed' => $counts['failed_count'],
            'pending' => $counts['pending_count'],
            'skipped' => $counts['skipped_count'],
        ];
    }

    public function retryFailedRecipients($campaignId)
    {
        $stmt = $this->db->prepare("
            UPDATE bulk_message_recipients
            SET status = 'pending', error_message = NULL, delivery_method = NULL, provider_response = NULL
            WHERE bulk_message_id = ? AND status = 'failed'
        ");
        $stmt->execute([$campaignId]);

        $retried = $stmt->rowCount();
        $result = $this->sendCampaign($campaignId);
        $result['retried'] = $retried;

        return $result;
    }

    public function getTemplates()
    {
        $stmt = $this->db->query("
            SELECT * FROM sms_templates
            WHERE is_active = 1
            ORDER BY category, name
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveCampaignCount()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS count FROM bulk_messages
            WHERE message_type = 'email' AND status IN ('sending', 'scheduled')
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($result['count'] ?? 0);
    }

    public function getSentCountToday()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total FROM bulk_message_recipients
            WHERE recipient_type = 'email' AND status = 'sent' AND DATE(sent_at) = CURDATE()
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($result['total'] ?? 0);
    }

    public function getTotalSent()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total FROM bulk_message_recipients
            WHERE recipient_type = 'email' AND status = 'sent'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($result['total'] ?? 0);
    }

    public function getFailedCount()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total FROM bulk_message_recipients
            WHERE recipient_type = 'email' AND status = 'failed'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($result['total'] ?? 0);
    }
}
