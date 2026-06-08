<?php

/**
 * cPanel cron-safe SMS campaign processor.
 *
 * Suggested cPanel cron command:
 *   /usr/local/bin/php /home/USER/shenacompanion.co.ke/tools/process_sms_campaigns.php
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

$batchSize = isset($argv[1]) ? max(1, (int)$argv[1]) : 50;
$maxCampaigns = isset($argv[2]) ? max(1, (int)$argv[2]) : 10;
$maxBatchesPerCampaign = isset($argv[3]) ? max(1, (int)$argv[3]) : 3;

try {
    $service = new BulkSmsService();
    $campaignResult = $service->processDueCampaigns($batchSize, $maxCampaigns, $maxBatchesPerCampaign);
    $queueResult = $service->processQueue(100);

    $summary = [
        'success' => true,
        'processed_at' => date('Y-m-d H:i:s'),
        'campaigns' => $campaignResult,
        'queue' => $queueResult,
    ];

    echo json_encode($summary, JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'SMS campaign processor failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
