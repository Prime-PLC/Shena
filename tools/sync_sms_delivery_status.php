<?php

/**
 * cPanel cron-safe SMS delivery status synchronizer.
 *
 * Suggested cPanel cron command:
 *   /usr/local/bin/php /home/USER/shenacompanion.co.ke/tools/sync_sms_delivery_status.php
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once CONFIG_PATH . '/config.php';
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/core/BaseModel.php';
require_once APP_PATH . '/services/EmailService.php';
require_once APP_PATH . '/services/SmsService.php';
require_once APP_PATH . '/services/NotificationService.php';
require_once APP_PATH . '/models/NotificationPreference.php';
require_once APP_PATH . '/services/BulkSmsService.php';

$limit = isset($argv[1]) ? max(1, (int)$argv[1]) : 100;

try {
    $service = new BulkSmsService();
    $result = $service->syncDeliveryStatuses($limit);

    echo json_encode([
        'success' => true,
        'processed_at' => date('Y-m-d H:i:s'),
        'delivery_sync' => $result,
    ], JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'SMS delivery status sync failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
