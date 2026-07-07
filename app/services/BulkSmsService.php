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
require_once __DIR__ . '/PaymentStatusService.php';

class BulkSmsService
{
    private $db;
    private $smsService;
    private $notificationService;
    private $notificationPreference;
    private $emailFallbackEnabled;
    private $paymentStatusService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->smsService = new SmsService();
        $this->notificationService = new NotificationService();
        $this->notificationPreference = new NotificationPreference();
        $this->emailFallbackEnabled = $this->getEmailFallbackSetting();
        $this->paymentStatusService = new PaymentStatusService();
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
        $targetAudience = $this->normalizeAudience($targetAudience);
        if ($this->isPaymentGroupAudience($targetAudience)) {
            return $this->getPaymentGroupRecipients($targetAudience, $customFilters);
        }
        if ($this->isAgentAudience($targetAudience)) {
            return $this->getAgentRecipients($targetAudience, $customFilters);
        }

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
        } elseif ($targetAudience === 'inactive') {
            $sql .= " AND m.status = ?";
            $params[] = 'inactive';
        } elseif ($targetAudience === 'pending') {
            $sql .= " AND m.status = ?";
            $params[] = 'pending';
        } elseif ($targetAudience === 'grace_period') {
            $sql .= " AND m.status = ?";
            $params[] = 'grace_period';
        } elseif ($targetAudience === 'defaulted') {
            $sql .= " AND m.status IN ('defaulted')";
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
            'payment_paid' => 'payment_paid_current',
            'payment_unpaid' => 'payment_unpaid_current',
            'payment_partial' => 'payment_partially_paid',
            'payment_arrears' => 'payment_in_arrears',
            'payment_defaulted' => 'payment_defaulted',
        ];

        return $map[$targetAudience] ?? ($targetAudience ?: 'all_members');
    }

    private function isPaymentGroupAudience(string $targetAudience): bool
    {
        return strpos($targetAudience, 'payment_') === 0;
    }

    private function isAgentAudience(string $targetAudience): bool
    {
        return in_array($targetAudience, ['agent_all', 'agent_active', 'agent_inactive', 'agent_with_members'], true);
    }

    private function getPaymentGroupRecipients(string $targetAudience, array $customFilters = []): array
    {
        $payment_group = substr($targetAudience, strlen('payment_'));
        $payment_group = $payment_group === 'all' ? 'all' : $payment_group;
        $rows = $this->paymentStatusService->getMembersByPaymentGroup($payment_group, $customFilters, 100000, 0);

        return array_map(static function (array $row): array {
            return [
                'user_id' => $row['user_id'] ?? null,
                'phone' => $row['phone'] ?? '',
                'email' => $row['email'] ?? '',
                'first_name' => $row['first_name'] ?? '',
                'last_name' => $row['last_name'] ?? '',
                'member_number' => $row['member_number'] ?? '',
                'package' => $row['package'] ?? '',
                'status' => $row['status'] ?? '',
                'member_status' => $row['status'] ?? '',
                'payment_group' => $row['payment_group'] ?? '',
                'monthly_contribution' => $row['monthly_contribution'] ?? 0,
                'paid_amount' => $row['paid_amount'] ?? 0,
                'balance_due' => $row['balance_due'] ?? 0,
                'amount_due' => $row['balance_due'] ?? 0,
                'arrears_amount' => $row['arrears_amount'] ?? 0,
                'missed_months' => $row['missed_months'] ?? 0,
                'last_payment_date' => $row['last_payment_date'] ?? '',
            ];
        }, $rows);
    }

    private function getAgentRecipients(string $targetAudience, array $customFilters = []): array
    {
        $sql = "SELECT u.id AS user_id, u.phone, u.email, u.first_name, u.last_name,
                       a.agent_number, a.status, a.total_members
                FROM agents a
                JOIN users u ON a.user_id = u.id
                WHERE u.phone IS NOT NULL AND u.phone != ''";
        $params = [];

        if ($targetAudience === 'agent_active') {
            $sql .= " AND a.status = ?";
            $params[] = 'active';
        } elseif ($targetAudience === 'agent_inactive') {
            $sql .= " AND a.status <> ?";
            $params[] = 'active';
        } elseif ($targetAudience === 'agent_with_members') {
            $sql .= " AND COALESCE(a.total_members, 0) > 0";
        }

        $sql .= " ORDER BY u.first_name ASC, u.last_name ASC, a.agent_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map(static function (array $row): array {
            return [
                'user_id' => $row['user_id'] ?? null,
                'phone' => $row['phone'] ?? '',
                'email' => $row['email'] ?? '',
                'first_name' => $row['first_name'] ?? '',
                'last_name' => $row['last_name'] ?? '',
                'member_number' => $row['agent_number'] ?? '',
                'package' => 'Agent',
                'status' => $row['status'] ?? '',
                'agent_number' => $row['agent_number'] ?? '',
                'total_members' => $row['total_members'] ?? 0,
                'amount_due' => 0,
            ];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
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

        if ($this->shouldRefreshRecipientsBeforeSend($campaign)) {
            $this->refreshRecipientsForCampaign((int)$bulkMessageId, $campaign);
            $campaign = $this->getCampaignById($bulkMessageId);
        }

        $batchSize = max(1, min((int) $batchSize, 500));
        $this->updateCampaignStatus($bulkMessageId, 'sending', true);

$sql = "SELECT bmr.*, u.first_name, u.last_name, u.email, u.phone,
                       m.member_number, m.package, m.status AS member_status,
                       COALESCE(m.monthly_contribution, 0) AS amount_due
                FROM bulk_message_recipients bmr
                INNER JOIN users u ON bmr.user_id = u.id
                LEFT JOIN members m ON u.id = m.user_id
                WHERE bmr.bulk_message_id = ? AND bmr.status = 'pending'
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bulkMessageId, $batchSize]);
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $emailFallbackCount = 0;
        $processedCount = 0;

        foreach ($recipients as $recipient) {
            $processedCount++;

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
                        $status = $deliveryMethod === 'email' ? 'sent' : 'submitted';
                        $this->updateRecipientStatus(
                            $recipient['id'],
                            $status,
                            null,
                            $deliveryMethod,
                            $result['provider_message_id'] ?? $result['data']['transactionId'] ?? null,
                            $result['provider_status'] ?? null,
                            $result['provider_cause'] ?? null,
                            $result
                        );
                    } else {
                        $this->updateRecipientStatus(
                            $recipient['id'],
                            'failed',
                            $result['error'] ?? 'Unknown error',
                            'failed',
                            null,
                            null,
                            null,
                            $result
                        );
                    }
                } else {
                    $result = $this->smsService->sendSms($recipient['recipient_value'], $message);

                    if (!empty($result['success'])) {
                        $this->updateRecipientStatus(
                            $recipient['id'],
                            'submitted',
                            null,
                            'sms',
                            $result['provider_message_id'] ?? $result['data']['transactionId'] ?? null,
                            $result['provider_status'] ?? null,
                            $result['provider_cause'] ?? null,
                            $result
                        );
                    } else {
                        $this->updateRecipientStatus(
                            $recipient['id'],
                            'failed',
                            $result['error'] ?? 'Unknown error',
                            'failed',
                            null,
                            null,
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
            $finalStatus = $this->campaignStatusFromCounts($counts);
            $this->updateCampaignStatus($bulkMessageId, $finalStatus, false, in_array($finalStatus, ['completed', 'failed'], true));
        }

        return [
            'success' => true,
            'sent_count' => (int) $counts['submitted_count'],
            'submitted_count' => (int) $counts['submitted_count'],
            'delivered_count' => (int) $counts['delivered_count'],
            'undelivered_count' => (int) $counts['undelivered_count'],
            'failed_count' => (int) $counts['failed_count'],
            'email_fallback_count' => $emailFallbackCount,
            'pending_count' => (int) $counts['pending_count'],
            'processed_count' => $processedCount
        ];
    }

    private function shouldRefreshRecipientsBeforeSend(array $campaign): bool
    {
        if (!in_array($campaign['status'] ?? '', ['draft', 'scheduled', 'paused'], true)) {
            return false;
        }

        $filters = $this->decodeCampaignFilters($campaign);
        $recipientMode = $filters['recipient_mode'] ?? 'refresh recipients';
        return $recipientMode !== 'saved list';
    }

    private function refreshRecipientsForCampaign(int $campaignId, array $campaign): void
    {
        $filters = $this->decodeCampaignFilters($campaign);
        $targetAudience = $campaign['target_audience'] ?? 'all_members';
        $recipients = $this->getRecipients($targetAudience, $filters);

        $this->db->prepare("DELETE FROM bulk_message_recipients WHERE bulk_message_id = ?")->execute([$campaignId]);
        if (!empty($recipients)) {
            $this->queueRecipients($campaignId, $recipients);
        } else {
            $this->syncCampaignRecipientTotals($campaignId, 0);
            $this->recalculateCampaignCounts($campaignId);
        }
    }

    private function decodeCampaignFilters(array $campaign): array
    {
        if (empty($campaign['custom_filters'])) {
            return [];
        }

        $decoded = json_decode((string)$campaign['custom_filters'], true);
        return is_array($decoded) ? $decoded : [];
    }

    public function sendCampaignUntilComplete($bulkMessageId, $batchSize = 50, $maxBatches = 10)
    {
        $summary = [
            'success' => true,
            'sent_count' => 0,
            'submitted_count' => 0,
            'delivered_count' => 0,
            'undelivered_count' => 0,
            'failed_count' => 0,
            'pending_count' => 0,
            'processed_count' => 0,
            'batches' => 0,
        ];

        $maxBatches = max(1, (int) $maxBatches);
        $batchSize = max(1, min((int) $batchSize, 500));

        for ($batch = 0; $batch < $maxBatches; $batch++) {
            $result = $this->sendCampaign($bulkMessageId, $batchSize);

            if (empty($result['success'])) {
                return $result;
            }

            $summary['batches']++;
            $summary['sent_count'] = (int) ($result['submitted_count'] ?? $result['sent_count']);
            $summary['submitted_count'] = (int) ($result['submitted_count'] ?? $result['sent_count']);
            $summary['delivered_count'] = (int) ($result['delivered_count'] ?? 0);
            $summary['undelivered_count'] = (int) ($result['undelivered_count'] ?? 0);
            $summary['failed_count'] = (int) $result['failed_count'];
            $summary['pending_count'] = (int) $result['pending_count'];
            $summary['processed_count'] += (int) ($result['processed_count'] ?? 0);

            if ((int) $result['pending_count'] === 0 || (int) ($result['processed_count'] ?? 0) === 0) {
                break;
            }
        }

        return $summary;
    }

    public function getDueCampaigns($limit = 10)
    {
        $sql = "SELECT bm.id
                FROM bulk_messages bm
                WHERE bm.message_type IN ('sms', 'both')
                  AND (
                    (bm.status = 'scheduled' AND bm.scheduled_at <= NOW())
                    OR bm.status = 'sending'
                  )
                ORDER BY COALESCE(bm.scheduled_at, bm.created_at) ASC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([max(1, (int) $limit)]);

        return array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id'));
    }

    public function processDueCampaigns($batchSize = 50, $maxCampaigns = 10, $maxBatchesPerCampaign = 3)
    {
        $campaignIds = $this->getDueCampaigns($maxCampaigns);
        $processedCampaigns = 0;
        $processedRecipients = 0;
        $submitted = 0;
        $delivered = 0;
        $undelivered = 0;
        $failed = 0;
        $pending = 0;
        $campaignResults = [];

        foreach ($campaignIds as $campaignId) {
            $result = $this->sendCampaignUntilComplete($campaignId, $batchSize, $maxBatchesPerCampaign);
            $processedCampaigns++;
            $processedRecipients += (int) ($result['processed_count'] ?? 0);
            $submitted += (int) ($result['submitted_count'] ?? $result['sent_count'] ?? 0);
            $delivered += (int) ($result['delivered_count'] ?? 0);
            $undelivered += (int) ($result['undelivered_count'] ?? 0);
            $failed += (int) ($result['failed_count'] ?? 0);
            $pending += (int) ($result['pending_count'] ?? 0);
            $campaignResults[] = [
                'campaign_id' => $campaignId,
                'success' => !empty($result['success']),
                'processed_count' => (int) ($result['processed_count'] ?? 0),
                'sent_count' => (int) ($result['submitted_count'] ?? $result['sent_count'] ?? 0),
                'submitted_count' => (int) ($result['submitted_count'] ?? $result['sent_count'] ?? 0),
                'delivered_count' => (int) ($result['delivered_count'] ?? 0),
                'undelivered_count' => (int) ($result['undelivered_count'] ?? 0),
                'failed_count' => (int) ($result['failed_count'] ?? 0),
                'pending_count' => (int) ($result['pending_count'] ?? 0),
            ];
        }

        return [
            'success' => true,
            'campaign_count' => $processedCampaigns,
            'processed_count' => $processedRecipients,
            'sent_count' => $submitted,
            'submitted_count' => $submitted,
            'delivered_count' => $delivered,
            'undelivered_count' => $undelivered,
            'failed_count' => $failed,
            'pending_count' => $pending,
            'campaigns' => $campaignResults,
        ];
    }

    public function getCampaignById($bulkMessageId)
    {
        $sql = "SELECT bm.*, u.email AS created_by_name,
                       COALESCE(stats.total_count, 0) AS total_recipients,
                       COALESCE(stats.submitted_count, 0) AS submitted_count,
                       COALESCE(stats.delivered_count, 0) AS delivered_count,
                       COALESCE(stats.delivered_count, 0) AS sent_count,
                       COALESCE(stats.failed_count, 0) AS failed_count,
                       COALESCE(stats.undelivered_count, 0) AS undelivered_count,
                       COALESCE(stats.pending_count, 0) AS pending_count
                FROM bulk_messages bm
                JOIN users u ON bm.created_by = u.id
                LEFT JOIN (
                    SELECT bulk_message_id,
                           COUNT(*) AS total_count,
                           SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted_count,
                           SUM(CASE WHEN status IN ('sent', 'delivered') THEN 1 ELSE 0 END) AS delivered_count,
                           SUM(CASE WHEN status IN ('failed', 'rejected') THEN 1 ELSE 0 END) AS failed_count,
                           SUM(CASE WHEN status IN ('undelivered', 'expired', 'unknown') THEN 1 ELSE 0 END) AS undelivered_count,
                           SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count
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
                       COALESCE(stats.submitted_count, 0) AS submitted_count,
                       COALESCE(stats.delivered_count, 0) AS delivered_count,
                       COALESCE(stats.delivered_count, 0) AS sent_count,
                       COALESCE(stats.failed_count, 0) AS failed_count,
                       COALESCE(stats.undelivered_count, 0) AS undelivered_count,
                       COALESCE(stats.pending_count, 0) AS pending_count,
                       ROUND((COALESCE(stats.delivered_count, 0) / NULLIF(COALESCE(stats.total_count, 0), 0) * 100), 2) AS success_rate
                FROM bulk_messages bm
                JOIN users u ON bm.created_by = u.id
                LEFT JOIN (
                    SELECT bulk_message_id,
                           COUNT(*) AS total_count,
                           SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted_count,
                           SUM(CASE WHEN status IN ('sent', 'delivered') THEN 1 ELSE 0 END) AS delivered_count,
                           SUM(CASE WHEN status IN ('failed', 'rejected') THEN 1 ELSE 0 END) AS failed_count,
                           SUM(CASE WHEN status IN ('undelivered', 'expired', 'unknown') THEN 1 ELSE 0 END) AS undelivered_count,
                           SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count
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

    private function updateRecipientStatus($recipientId, $status, $errorMessage = null, $deliveryMethod = null, $providerMessageId = null, $providerStatus = null, $providerCause = null, $providerResponse = null)
    {
        $sql = "UPDATE bulk_message_recipients
                SET status = ?,
                    sent_at = CASE WHEN ? IN ('sent', 'delivered') THEN NOW() ELSE sent_at END,
                    submitted_at = CASE WHEN ? = 'submitted' THEN NOW() ELSE submitted_at END,
                    delivered_at = CASE WHEN ? IN ('sent', 'delivered') THEN COALESCE(delivered_at, NOW()) ELSE delivered_at END,
                    error_message = ?,
                    delivery_method = ?,
                    provider_message_id = ?,
                    provider_status = ?,
                    provider_cause = ?,
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
            $status,
            $status,
            $errorMessage,
            $deliveryMethod,
            $providerMessageId,
            $providerStatus,
            $providerCause,
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
                    SUM(CASE WHEN bmr.status = 'submitted' THEN 1 ELSE 0 END) AS submitted_count,
                    SUM(CASE WHEN bmr.status IN ('sent', 'delivered') THEN 1 ELSE 0 END) AS delivered_count,
                    SUM(CASE WHEN bmr.status IN ('sent', 'delivered') THEN 1 ELSE 0 END) AS sent_count,
                    SUM(CASE WHEN bmr.status IN ('failed', 'rejected') THEN 1 ELSE 0 END) AS failed_count,
                    SUM(CASE WHEN bmr.status IN ('undelivered', 'expired', 'unknown') THEN 1 ELSE 0 END) AS undelivered_count,
                    SUM(CASE WHEN bmr.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                    SUM(CASE WHEN bmr.status = 'skipped' THEN 1 ELSE 0 END) AS skipped_count
                FROM bulk_message_recipients bmr
                WHERE bmr.bulk_message_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bulkMessageId]);
        $counts = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $updateSql = "UPDATE bulk_messages
                      SET total_recipients = ?,
                          sent_count = ?,
                          submitted_count = ?,
                          delivered_count = ?,
                          undelivered_count = ?,
                          failed_count = ?
                      WHERE id = ?";
        $this->db->prepare($updateSql)->execute([
            (int) ($counts['total_count'] ?? 0),
            (int) ($counts['sent_count'] ?? 0),
            (int) ($counts['submitted_count'] ?? 0),
            (int) ($counts['delivered_count'] ?? 0),
            (int) ($counts['undelivered_count'] ?? 0),
            (int) ($counts['failed_count'] ?? 0),
            $bulkMessageId
        ]);

        return [
            'total_count' => (int) ($counts['total_count'] ?? 0),
            'sent_count' => (int) ($counts['sent_count'] ?? 0),
            'submitted_count' => (int) ($counts['submitted_count'] ?? 0),
            'delivered_count' => (int) ($counts['delivered_count'] ?? 0),
            'undelivered_count' => (int) ($counts['undelivered_count'] ?? 0),
            'failed_count' => (int) ($counts['failed_count'] ?? 0),
            'pending_count' => (int) ($counts['pending_count'] ?? 0),
            'skipped_count' => (int) ($counts['skipped_count'] ?? 0),
        ];
    }

    private function campaignStatusFromCounts(array $counts)
    {
        $pending = (int) ($counts['pending_count'] ?? 0);
        $submitted = (int) ($counts['submitted_count'] ?? 0);
        $delivered = (int) ($counts['delivered_count'] ?? $counts['sent_count'] ?? 0);
        $failed = (int) ($counts['failed_count'] ?? 0);
        $undelivered = (int) ($counts['undelivered_count'] ?? 0);
        $total = (int) ($counts['total_count'] ?? 0);

        if ($pending > 0) {
            return 'sending';
        }
        if ($submitted > 0) {
            return $delivered > 0 || $failed > 0 || $undelivered > 0 ? 'partially_delivered' : 'submitted';
        }
        if ($delivered > 0 && ($failed > 0 || $undelivered > 0)) {
            return 'partially_delivered';
        }
        if ($delivered > 0 && $delivered === $total) {
            return 'completed';
        }
        if ($failed > 0 || $undelivered > 0) {
            return $delivered > 0 ? 'partially_delivered' : 'failed';
        }

        return 'failed';
    }

    public function deleteCampaign($bulkMessageId)
    {
        $campaign = $this->getCampaignById($bulkMessageId);
        if (!$campaign || $campaign['status'] === 'sending') {
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
            'submitted' => $counts['submitted_count'],
            'delivered' => $counts['delivered_count'],
            'undelivered' => $counts['undelivered_count'],
            'failed' => $counts['failed_count'],
            'pending' => $counts['pending_count'],
            'skipped' => $counts['skipped_count'],
        ];
    }

    public function refreshCampaignDeliveryStatus($bulkMessageId)
    {
        $counts = $this->recalculateCampaignCounts($bulkMessageId);
        $this->updateCampaignStatus(
            $bulkMessageId,
            $this->campaignStatusFromCounts($counts),
            false,
            (int) $counts['submitted_count'] === 0 && (int) $counts['pending_count'] === 0
        );
        $this->db->prepare("UPDATE bulk_messages SET dlr_synced_at = NOW() WHERE id = ?")->execute([$bulkMessageId]);

        return $counts;
    }

    public function getActiveCampaignCount()
    {
        $sql = "SELECT COUNT(*) AS count FROM bulk_messages
                WHERE message_type IN ('sms', 'both') AND status IN ('sending', 'scheduled', 'submitted', 'partially_delivered')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    public function getSentCountToday()
    {
        $sql = "SELECT COUNT(*) AS count
                FROM bulk_message_recipients
                WHERE recipient_type = 'sms' AND status IN ('sent', 'delivered') AND DATE(COALESCE(delivered_at, sent_at)) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    public function getTotalSentCount()
    {
        $sql = "SELECT COUNT(*) AS count FROM bulk_message_recipients
                WHERE recipient_type = 'sms' AND status IN ('sent', 'delivered')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    public function getFailedCount()
    {
        $sql = "SELECT COUNT(*) AS count FROM bulk_message_recipients
                WHERE recipient_type = 'sms' AND status IN ('failed', 'undelivered', 'expired', 'rejected', 'unknown')";
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
        $accountStatus = $this->smsService->getAccountStatus();
        if (!empty($accountStatus['success']) && $accountStatus['sms_balance'] !== null) {
            return $accountStatus['sms_balance'];
        }

        $sql = "SELECT balance FROM sms_credits LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['balance'] ?? 0;
    }

    public function getQueueItems($filters = [], $limit = 50, $offset = 0)
    {
        if (is_int($filters)) {
            $limit = $filters;
            $filters = [];
        }

        [$where, $params] = $this->buildQueueFilterClause($filters);
        $limit = max(1, min((int) $limit, 200));
        $offset = max(0, (int) $offset);

        $sql = "SELECT sq.*, u.first_name, u.last_name, m.member_number
                FROM sms_queue sq
                LEFT JOIN users u ON sq.user_id = u.id
                LEFT JOIN members m ON u.id = m.user_id
                {$where}
                ORDER BY sq.created_at DESC, sq.id DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($params, [$limit, $offset]));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQueueItemsCount(array $filters = [])
    {
        [$where, $params] = $this->buildQueueFilterClause($filters);
        $stmt = $this->db->prepare("SELECT COUNT(*) AS count
                                    FROM sms_queue sq
                                    LEFT JOIN users u ON sq.user_id = u.id
                                    LEFT JOIN members m ON u.id = m.user_id
                                    {$where}");
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    private function buildQueueFilterClause(array $filters = [])
    {
        $where = [];
        $params = [];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = 'sq.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(sq.phone_number LIKE ? OR sq.message LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR m.member_number LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            array_push($params, $term, $term, $term, $term, $term);
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'sq.created_at >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'sq.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        return [empty($where) ? '' : 'WHERE ' . implode(' AND ', $where), $params];
    }

    public function queueQuickSms(array $recipients, string $message, int $createdBy = 0)
    {
        $stmt = $this->db->prepare("
            INSERT INTO sms_queue (
                phone_number, message, priority, status, user_id, scheduled_at, bulk_message_id
            ) VALUES (?, ?, 'normal', 'pending', ?, NULL, NULL)
        ");

        $ids = [];
        foreach ($recipients as $recipient) {
            $phone = $this->smsService->formatPhoneNumber($recipient['phone'] ?? '');
            $personalized = $this->replacePlaceholders($message, $recipient);
            $stmt->execute([
                $phone,
                $personalized,
                $recipient['user_id'] ?? null,
            ]);
            $ids[] = (int) $this->db->lastInsertId();
        }

        return $ids;
    }

    public function getTemplates()
    {
        $sql = "SELECT * FROM sms_templates WHERE is_active = 1 ORDER BY category, name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createDraftFromPaymentGroup(array $filters, int $createdBy)
    {
        $paymentGroup = $filters['payment_group'] ?? $filters['group'] ?? 'unpaid_current';
        $targetAudience = 'payment_' . $paymentGroup;
        $title = $filters['title'] ?? $this->paymentCampaignTitle($paymentGroup);
        $message = $filters['message'] ?? $this->paymentCampaignMessage($paymentGroup);
        $customFilters = $filters;
        $customFilters['payment_group'] = $paymentGroup;
        $customFilters['recipient_mode'] = 'refresh recipients';

        $campaignId = $this->createCampaign([
            'title' => $title,
            'message' => $message,
            'target_audience' => $targetAudience,
            'custom_filters' => $customFilters,
            'scheduled_at' => null,
        ], $createdBy);

        if (!$campaignId) {
            return false;
        }

        $recipients = $this->getRecipients($targetAudience, $customFilters);
        if (!empty($recipients)) {
            $this->queueRecipients($campaignId, $recipients);
        }

        return $campaignId;
    }

    public function reuseCampaignAsDraft(int $campaignId, int $createdBy)
    {
        $campaign = $this->getCampaignById($campaignId);
        if (!$campaign) {
            return false;
        }

        $filters = $this->decodeCampaignFilters($campaign);

        $newCampaignId = $this->createCampaign([
            'title' => 'Copy of ' . ($campaign['title'] ?? 'SMS Campaign'),
            'message' => $campaign['message'] ?? '',
            'target_audience' => $campaign['target_audience'] ?? 'all_members',
            'custom_filters' => $filters + ['recipient_mode' => 'refresh recipients'],
            'scheduled_at' => null,
        ], $createdBy);

        if (!$newCampaignId) {
            return false;
        }

        $recipients = $this->getRecipients($campaign['target_audience'] ?? 'all_members', $filters);
        if (!empty($recipients)) {
            $this->queueRecipients($newCampaignId, $recipients);
        }

        return $newCampaignId;
    }

    public function resendSingleRecipient(int $campaignId, int $recipientId)
    {
        $campaign = $this->getCampaignById($campaignId);
        if (!$campaign) {
            return ['success' => false, 'message' => 'Campaign not found'];
        }

        $sql = "SELECT bmr.*, u.first_name, u.last_name, u.email, u.phone,
                       m.member_number, m.package, m.status AS member_status,
                       COALESCE(m.monthly_contribution, 0) AS amount_due
                FROM bulk_message_recipients bmr
                JOIN users u ON bmr.user_id = u.id
                LEFT JOIN members m ON u.id = m.user_id
                WHERE bmr.bulk_message_id = ? AND bmr.id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$campaignId, $recipientId]);
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipient) {
            return ['success' => false, 'message' => 'Recipient not found'];
        }
        if (!in_array($recipient['status'], ['failed', 'undelivered', 'expired', 'rejected', 'unknown'], true)) {
            return ['success' => false, 'message' => 'Only failed or undelivered SMS can be resent individually'];
        }

        $phone = $this->smsService->formatPhoneNumber($recipient['recipient_value'] ?? $recipient['phone'] ?? '');
        $message = $this->replacePlaceholders($campaign['message'] ?? '', $recipient);
        if (!$this->smsService->validatePhoneNumber($phone)) {
            $this->updateRecipientStatus($recipientId, 'failed', 'invalid_phone', 'failed');
            return ['success' => false, 'message' => 'Recipient phone number is invalid'];
        }

        $this->updateRecipientStatus($recipientId, 'pending', null, null, null, null, null, null);
        $result = $this->smsService->sendSms($phone, $message);
        if (!empty($result['success'])) {
            $this->updateRecipientStatus(
                $recipientId,
                'submitted',
                null,
                'sms',
                $result['provider_message_id'] ?? $result['data']['transactionId'] ?? null,
                $result['provider_status'] ?? null,
                $result['provider_cause'] ?? null,
                $result
            );
            $this->recalculateCampaignCounts($campaignId);
            return ['success' => true, 'message' => 'SMS resent to this recipient. Delivery confirmation will update after sync.'];
        }

        $this->updateRecipientStatus($recipientId, 'failed', $result['error'] ?? 'Failed to resend SMS', 'failed', null, null, null, $result);
        $this->recalculateCampaignCounts($campaignId);
        return ['success' => false, 'message' => $result['error'] ?? 'Failed to resend SMS'];
    }

    private function paymentCampaignTitle(string $paymentGroup): string
    {
        return $this->paymentStatusService->labelForGroup($paymentGroup) . ' Reminder';
    }

    private function paymentCampaignMessage(string $paymentGroup): string
    {
        $templates = [
            'paid_current' => 'Dear {first_name}, thank you for keeping your SHENA contribution up to date. We appreciate your loyalty.',
            'partially_paid' => 'Dear {first_name}, thank you for your payment. Your remaining payment balance is KES {amount_due}. Please clear it when possible.',
            'in_arrears' => 'Dear {first_name}, your SHENA outstanding balance is KES {arrears_amount} for {missed_months} missed month(s). Please clear it to keep your benefits active.',
            'defaulted' => 'Dear {first_name}, your SHENA account has an outstanding balance of KES {arrears_amount}. Please contact us or clear the balance to restore your account.',
            'unpaid_current' => 'Dear {first_name}, our records show your monthly SHENA contribution of KES {monthly_contribution} has not been received. Please pay by Paybill 4163987.',
        ];

        return $templates[$paymentGroup] ?? 'Dear {first_name}, this is a SHENA payment update. Your current payment balance is KES {amount_due}.';
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
                       m.id AS member_id,
                       m.member_number, m.package, m.status AS member_status,
                       bmr.delivery_method, bmr.email_fallback_sent, bmr.email_sent_at,
                       bmr.provider_message_id, bmr.provider_status, bmr.provider_cause,
                       bmr.provider_response, bmr.submitted_at, bmr.delivered_at,
                       bmr.dlr_checked_at, bmr.dlr_attempts
                FROM bulk_message_recipients bmr
                JOIN users u ON bmr.user_id = u.id
                LEFT JOIN members m ON u.id = m.user_id
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

    public function resetRecipientsForResend($campaignId, array $statuses = ['pending', 'failed'])
    {
        $allowed = array_values(array_intersect($statuses, ['pending', 'failed']));
        if (empty($allowed)) {
            $allowed = ['pending', 'failed'];
        }

        $placeholders = implode(',', array_fill(0, count($allowed), '?'));
        $sql = "UPDATE bulk_message_recipients
                SET status = 'pending',
                    error_message = NULL,
                    sent_at = NULL,
                    delivery_method = NULL,
                    provider_message_id = NULL,
                    provider_response = NULL,
                    email_fallback_sent = 0,
                    email_sent_at = NULL
                WHERE bulk_message_id = ?
                  AND status IN ({$placeholders})";
        $params = array_merge([$campaignId], $allowed);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $this->recalculateCampaignCounts($campaignId);

        return $stmt->rowCount();
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
                    $this->updateQueueStatus(
                        $item['id'],
                        'submitted',
                        null,
                        $result['provider_message_id'] ?? $result['data']['transactionId'] ?? null,
                        $result['provider_status'] ?? null,
                        $result['provider_cause'] ?? null,
                        $result
                    );
                    $sentCount++;
                } else {
                    $this->updateQueueStatus($item['id'], 'failed', $result['error'] ?? 'Unknown error', null, null, null, $result);
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
            'submitted_count' => $sentCount,
            'failed_count' => $failedCount
        ];
    }

    public function processQueueByIds(array $queueIds)
    {
        $queueIds = array_values(array_filter(array_map('intval', $queueIds)));
        if (empty($queueIds)) {
            return ['sent_count' => 0, 'submitted_count' => 0, 'failed_count' => 0, 'processed_count' => 0];
        }

        $placeholders = implode(',', array_fill(0, count($queueIds), '?'));
        $stmt = $this->db->prepare("SELECT * FROM sms_queue WHERE status = 'pending' AND id IN ({$placeholders}) ORDER BY id ASC");
        $stmt->execute($queueIds);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $submitted = 0;
        $failed = 0;
        $processed = 0;

        foreach ($items as $item) {
            $processed++;
            try {
                $result = $this->smsService->sendSms($item['phone_number'], $item['message']);
                if (!empty($result['success'])) {
                    $this->updateQueueStatus(
                        $item['id'],
                        'submitted',
                        null,
                        $result['provider_message_id'] ?? $result['data']['transactionId'] ?? null,
                        $result['provider_status'] ?? null,
                        $result['provider_cause'] ?? null,
                        $result
                    );
                    $submitted++;
                } else {
                    $this->updateQueueStatus($item['id'], 'failed', $result['error'] ?? 'Unknown error', null, null, null, $result);
                    $failed++;
                }
            } catch (Throwable $e) {
                $this->updateQueueStatus($item['id'], 'failed', $e->getMessage());
                $failed++;
            }
        }

        return [
            'sent_count' => $submitted,
            'submitted_count' => $submitted,
            'failed_count' => $failed,
            'processed_count' => $processed
        ];
    }

    private function updateQueueStatus($queueId, $status, $errorMessage = null, $providerMessageId = null, $providerStatus = null, $providerCause = null, $providerResponse = null)
    {
        $sql = "UPDATE sms_queue
                SET status = ?,
                    sent_at = CASE WHEN ? IN ('sent', 'delivered') THEN NOW() ELSE sent_at END,
                    submitted_at = CASE WHEN ? = 'submitted' THEN NOW() ELSE submitted_at END,
                    delivered_at = CASE WHEN ? IN ('sent', 'delivered') THEN COALESCE(delivered_at, NOW()) ELSE delivered_at END,
                    error_message = ?,
                    provider_message_id = COALESCE(?, provider_message_id),
                    provider_status = ?,
                    provider_cause = ?,
                    provider_response = ?
                WHERE id = ?";
        $encodedResponse = $providerResponse === null ? null : json_encode($providerResponse);
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $status,
            $status,
            $status,
            $status,
            $errorMessage,
            $providerMessageId,
            $providerStatus,
            $providerCause,
            $encodedResponse,
            $queueId
        ]);
    }

    public function syncDeliveryStatuses($limit = 100)
    {
        $limit = max(1, min((int) $limit, 500));

        $recipientResult = $this->syncCampaignRecipientDeliveryStatuses($limit);
        $remaining = max(0, $limit - (int) ($recipientResult['checked_count'] ?? 0));
        $queueResult = $this->syncQueueDeliveryStatuses($remaining > 0 ? $remaining : $limit);

        return [
            'success' => true,
            'checked_count' => (int) ($recipientResult['checked_count'] ?? 0) + (int) ($queueResult['checked_count'] ?? 0),
            'updated_count' => (int) ($recipientResult['updated_count'] ?? 0) + (int) ($queueResult['updated_count'] ?? 0),
            'delivered_count' => (int) ($recipientResult['delivered_count'] ?? 0) + (int) ($queueResult['delivered_count'] ?? 0),
            'undelivered_count' => (int) ($recipientResult['undelivered_count'] ?? 0) + (int) ($queueResult['undelivered_count'] ?? 0),
            'failed_checks' => (int) ($recipientResult['failed_checks'] ?? 0) + (int) ($queueResult['failed_checks'] ?? 0),
            'campaign_ids' => array_values(array_unique($recipientResult['campaign_ids'] ?? [])),
        ];
    }

    private function syncCampaignRecipientDeliveryStatuses($limit)
    {
        $sql = "SELECT id, bulk_message_id, provider_message_id
                FROM bulk_message_recipients
                WHERE recipient_type = 'sms'
                  AND status = 'submitted'
                  AND provider_message_id IS NOT NULL
                  AND (dlr_checked_at IS NULL OR dlr_checked_at <= DATE_SUB(NOW(), INTERVAL 10 MINUTE))
                ORDER BY submitted_at ASC, id ASC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [
            'checked_count' => 0,
            'updated_count' => 0,
            'delivered_count' => 0,
            'undelivered_count' => 0,
            'failed_checks' => 0,
            'campaign_ids' => [],
        ];

        foreach ($rows as $row) {
            $summary['checked_count']++;
            $dlr = $this->smsService->checkDeliveryStatus($row['provider_message_id']);

            if (empty($dlr['success'])) {
                $summary['failed_checks']++;
                $this->markRecipientDlrChecked((int) $row['id'], null, $dlr['error'] ?? 'DLR check failed', $dlr);
                continue;
            }

            $status = $dlr['status'] ?? 'unknown';
            $this->markRecipientDlrChecked(
                (int) $row['id'],
                $status,
                $dlr['provider_cause'] ?? null,
                $dlr,
                $dlr['provider_status'] ?? null,
                $dlr['delivered_at'] ?? null
            );

            if ($status !== 'submitted') {
                $summary['updated_count']++;
                $summary['campaign_ids'][] = (int) $row['bulk_message_id'];
            }
            if ($status === 'delivered') {
                $summary['delivered_count']++;
            } elseif (in_array($status, ['undelivered', 'expired', 'rejected', 'unknown'], true)) {
                $summary['undelivered_count']++;
            }
        }

        foreach (array_unique($summary['campaign_ids']) as $campaignId) {
            $counts = $this->recalculateCampaignCounts($campaignId);
            $this->updateCampaignStatus($campaignId, $this->campaignStatusFromCounts($counts), false, (int) $counts['submitted_count'] === 0 && (int) $counts['pending_count'] === 0);
            $this->db->prepare("UPDATE bulk_messages SET dlr_synced_at = NOW() WHERE id = ?")->execute([$campaignId]);
        }

        return $summary;
    }

    private function markRecipientDlrChecked($recipientId, $status = null, $providerCause = null, $providerResponse = null, $providerStatus = null, $deliveredAt = null)
    {
        $sql = "UPDATE bulk_message_recipients
                SET status = COALESCE(?, status),
                    provider_status = COALESCE(?, provider_status),
                    provider_cause = COALESCE(?, provider_cause),
                    provider_response = COALESCE(?, provider_response),
                    delivered_at = CASE WHEN ? = 'delivered' THEN COALESCE(?, NOW()) ELSE delivered_at END,
                    sent_at = CASE WHEN ? = 'delivered' THEN COALESCE(sent_at, NOW()) ELSE sent_at END,
                    dlr_checked_at = NOW(),
                    dlr_attempts = COALESCE(dlr_attempts, 0) + 1
                WHERE id = ?";
        $encodedResponse = $providerResponse === null ? null : json_encode($providerResponse);
        return $this->db->prepare($sql)->execute([
            $status,
            $providerStatus,
            $providerCause,
            $encodedResponse,
            $status,
            $deliveredAt,
            $status,
            $recipientId
        ]);
    }

    private function syncQueueDeliveryStatuses($limit)
    {
        $sql = "SELECT id, provider_message_id
                FROM sms_queue
                WHERE status = 'submitted'
                  AND provider_message_id IS NOT NULL
                  AND (dlr_checked_at IS NULL OR dlr_checked_at <= DATE_SUB(NOW(), INTERVAL 10 MINUTE))
                ORDER BY submitted_at ASC, id ASC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [
            'checked_count' => 0,
            'updated_count' => 0,
            'delivered_count' => 0,
            'undelivered_count' => 0,
            'failed_checks' => 0,
        ];

        foreach ($rows as $row) {
            $summary['checked_count']++;
            $dlr = $this->smsService->checkDeliveryStatus($row['provider_message_id']);

            if (empty($dlr['success'])) {
                $summary['failed_checks']++;
                $this->markQueueDlrChecked((int) $row['id'], null, $dlr['error'] ?? 'DLR check failed', $dlr);
                continue;
            }

            $status = $dlr['status'] ?? 'unknown';
            $this->markQueueDlrChecked(
                (int) $row['id'],
                $status,
                $dlr['provider_cause'] ?? null,
                $dlr,
                $dlr['provider_status'] ?? null,
                $dlr['delivered_at'] ?? null
            );

            if ($status !== 'submitted') {
                $summary['updated_count']++;
            }
            if ($status === 'delivered') {
                $summary['delivered_count']++;
            } elseif (in_array($status, ['undelivered', 'expired', 'rejected', 'unknown'], true)) {
                $summary['undelivered_count']++;
            }
        }

        return $summary;
    }

    private function markQueueDlrChecked($queueId, $status = null, $providerCause = null, $providerResponse = null, $providerStatus = null, $deliveredAt = null)
    {
        $sql = "UPDATE sms_queue
                SET status = COALESCE(?, status),
                    provider_status = COALESCE(?, provider_status),
                    provider_cause = COALESCE(?, provider_cause),
                    provider_response = COALESCE(?, provider_response),
                    delivered_at = CASE WHEN ? = 'delivered' THEN COALESCE(?, NOW()) ELSE delivered_at END,
                    sent_at = CASE WHEN ? = 'delivered' THEN COALESCE(sent_at, NOW()) ELSE sent_at END,
                    dlr_checked_at = NOW(),
                    dlr_attempts = COALESCE(dlr_attempts, 0) + 1
                WHERE id = ?";
        $encodedResponse = $providerResponse === null ? null : json_encode($providerResponse);
        return $this->db->prepare($sql)->execute([
            $status,
            $providerStatus,
            $providerCause,
            $encodedResponse,
            $status,
            $deliveredAt,
            $status,
            $queueId
        ]);
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
            'monthly_contribution' => number_format((float) ($recipient['monthly_contribution'] ?? $recipient['amount_due'] ?? 0), 2),
            'contribution' => number_format((float) ($recipient['monthly_contribution'] ?? $recipient['amount_due'] ?? 0), 2),
            'paid_amount' => number_format((float) ($recipient['paid_amount'] ?? 0), 2),
            'balance_due' => number_format((float) ($recipient['balance_due'] ?? $recipient['amount_due'] ?? 0), 2),
            'balance' => number_format((float) ($recipient['balance_due'] ?? $recipient['amount_due'] ?? 0), 2),
            'arrears_amount' => number_format((float) ($recipient['arrears_amount'] ?? $recipient['amount_due'] ?? 0), 2),
            'outstanding_balance' => number_format((float) ($recipient['arrears_amount'] ?? $recipient['amount_due'] ?? 0), 2),
            'missed_months' => (string)($recipient['missed_months'] ?? 0),
            'last_payment_date' => $recipient['last_payment_date'] ?? '',
            'agent_number' => $recipient['agent_number'] ?? '',
            'agent_no' => $recipient['agent_number'] ?? '',
            'total_members' => (string)($recipient['total_members'] ?? ''),
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
