<?php

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once CONFIG_PATH . '/config.php';
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/services/SmsService.php';

header('Content-Type: application/json');

try {
    $secret = defined('HOSTPINNACLE_SMS_WEBHOOK_SECRET') ? HOSTPINNACLE_SMS_WEBHOOK_SECRET : '';
    if ($secret !== '') {
        $provided = $_GET['token'] ?? $_POST['token'] ?? ($_SERVER['HTTP_X_WEBHOOK_TOKEN'] ?? '');
        if (!hash_equals($secret, (string) $provided)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid webhook token']);
            exit;
        }
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $transactionId = $payload['transactionId']
        ?? $payload['transaction_id']
        ?? $payload['messageId']
        ?? $payload['message_id']
        ?? null;

    if (!$transactionId) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Missing transaction ID']);
        exit;
    }

    $smsService = new SmsService();
    $providerStatus = $payload['Status']
        ?? $payload['status']
        ?? $payload['deliveryStatus']
        ?? $payload['messageStatus']
        ?? 'unknown';
    $status = $smsService->normalizeProviderStatus($providerStatus);
    $providerCause = $payload['Cause']
        ?? $payload['cause']
        ?? $payload['reason']
        ?? $payload['message']
        ?? null;

    $deliveredAt = null;
    foreach (['DeliveredTime', 'deliveredTime', 'delivered_at', 'deliveryTime'] as $timeKey) {
        if (!empty($payload[$timeKey])) {
            $timestamp = strtotime((string) $payload[$timeKey]);
            $deliveredAt = $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
            break;
        }
    }

    $db = Database::getInstance()->getConnection();
    $encodedPayload = json_encode($payload);

    $recipientSql = "UPDATE bulk_message_recipients
                     SET status = ?,
                         provider_status = ?,
                         provider_cause = ?,
                         provider_response = ?,
                         delivered_at = CASE WHEN ? = 'delivered' THEN COALESCE(?, NOW()) ELSE delivered_at END,
                         sent_at = CASE WHEN ? = 'delivered' THEN COALESCE(sent_at, NOW()) ELSE sent_at END,
                         dlr_checked_at = NOW(),
                         dlr_attempts = COALESCE(dlr_attempts, 0) + 1
                     WHERE provider_message_id = ?";
    $recipientStmt = $db->prepare($recipientSql);
    $recipientStmt->execute([
        $status,
        $providerStatus,
        $providerCause,
        $encodedPayload,
        $status,
        $deliveredAt,
        $status,
        $transactionId
    ]);

    $queueSql = "UPDATE sms_queue
                 SET status = ?,
                     provider_status = ?,
                     provider_cause = ?,
                     provider_response = ?,
                     delivered_at = CASE WHEN ? = 'delivered' THEN COALESCE(?, NOW()) ELSE delivered_at END,
                     sent_at = CASE WHEN ? = 'delivered' THEN COALESCE(sent_at, NOW()) ELSE sent_at END,
                     dlr_checked_at = NOW(),
                     dlr_attempts = COALESCE(dlr_attempts, 0) + 1
                 WHERE provider_message_id = ?";
    $queueStmt = $db->prepare($queueSql);
    $queueStmt->execute([
        $status,
        $providerStatus,
        $providerCause,
        $encodedPayload,
        $status,
        $deliveredAt,
        $status,
        $transactionId
    ]);

    $campaignRows = $db->prepare("SELECT DISTINCT bulk_message_id FROM bulk_message_recipients WHERE provider_message_id = ?");
    $campaignRows->execute([$transactionId]);
    $campaignIds = array_filter(array_map('intval', array_column($campaignRows->fetchAll(PDO::FETCH_ASSOC), 'bulk_message_id')));

    if ($campaignIds) {
        require_once APP_PATH . '/core/BaseModel.php';
        require_once APP_PATH . '/services/EmailService.php';
        require_once APP_PATH . '/services/NotificationService.php';
        require_once APP_PATH . '/models/NotificationPreference.php';
        require_once APP_PATH . '/services/BulkSmsService.php';
        $bulkSmsService = new BulkSmsService();
        foreach ($campaignIds as $campaignId) {
            $bulkSmsService->refreshCampaignDeliveryStatus($campaignId);
        }
    }

    echo json_encode([
        'success' => true,
        'transaction_id' => $transactionId,
        'status' => $status,
        'recipient_updates' => $recipientStmt->rowCount(),
        'queue_updates' => $queueStmt->rowCount(),
    ]);
} catch (Throwable $e) {
    error_log('SMS delivery callback error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Callback processing failed']);
}
