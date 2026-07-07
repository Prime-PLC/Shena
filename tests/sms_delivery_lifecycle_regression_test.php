<?php

$root = dirname(__DIR__);

$assertContains = function (string $haystack, string $needle, string $message): void {
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, "Assertion failed: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
};

$assertNotContains = function (string $haystack, string $needle, string $message): void {
    if (strpos($haystack, $needle) !== false) {
        fwrite(STDERR, "Assertion failed: {$message}\nUnexpected: {$needle}\n");
        exit(1);
    }
};

$smsService = file_get_contents($root . '/app/services/SmsService.php');
$bulkSmsService = file_get_contents($root . '/app/services/BulkSmsService.php');
$controller = file_get_contents($root . '/app/controllers/BulkSmsController.php');
$router = file_get_contents($root . '/app/core/Router.php');
$smsView = file_get_contents($root . '/resources/views/admin/sms-campaigns.php');
$detailsView = file_get_contents($root . '/resources/views/admin/campaign-details.php');
$migration = file_get_contents($root . '/database/migrations/014_sms_delivery_lifecycle.sql');

$assertContains($smsService, 'checkDeliveryStatus', 'SmsService should expose HostPinnacle DLR checks.');
$assertContains($smsService, 'getAccountStatus', 'SmsService should expose HostPinnacle account balance checks.');
$assertContains($smsService, "'status' => 'submitted'", 'SmsService submission success should be returned as submitted.');
$assertContains($smsService, 'CURLOPT_SSL_VERIFYPEER, true', 'SmsService should verify SSL in production requests.');

$assertContains($bulkSmsService, "'submitted'", 'Bulk SMS service should use submitted status.');
$assertContains($bulkSmsService, 'syncDeliveryStatuses', 'Bulk SMS service should sync provider delivery reports.');
$assertContains($bulkSmsService, "status = 'submitted'", 'DLR sync should process submitted records.');
$assertContains($bulkSmsService, 'PaymentStatusService', 'Defaulter targeting should use shared payment deadline grouping.');
$assertContains($bulkSmsService, 'payment_defaulted', 'Payment defaulter campaigns should target the calculated defaulted payment group.');
$assertNotContains($bulkSmsService, "DATE_SUB(CURDATE(), INTERVAL 60 DAY)", 'Defaulter targeting should not use rough 60-day no-payment grouping.');
$assertNotContains($bulkSmsService, "status = 'sent', sent_at", 'Campaign send path should not mark provider submissions as sent.');

$assertContains($controller, 'syncDeliveryStatuses', 'Controller should expose manual DLR sync.');
$assertContains($router, '/admin/communications/sync-delivery-statuses', 'Router should expose manual DLR sync endpoint.');
$assertContains($smsView, 'Sync Delivery', 'SMS campaign UI should expose a delivery sync action.');
$assertContains($smsView, 'Delivered Today', 'SMS campaign UI should distinguish delivered count from submitted count.');
$assertContains($detailsView, 'Submitted', 'Campaign details should show submitted count.');
$assertContains($detailsView, 'Delivered', 'Campaign details should show delivered count.');

$assertContains($migration, "ADD COLUMN submitted_count", 'Migration should add submitted campaign count.');
$assertContains($migration, "ADD COLUMN provider_status", 'Migration should add provider status tracking.');
$assertContains($migration, "ALTER TABLE sms_queue", 'Migration should add queue provider tracking.');

if (!file_exists($root . '/tools/sync_sms_delivery_status.php')) {
    fwrite(STDERR, "Assertion failed: SMS DLR sync cron tool is missing\n");
    exit(1);
}
if (!file_exists($root . '/public/sms-delivery-callback.php')) {
    fwrite(STDERR, "Assertion failed: SMS DLR callback endpoint is missing\n");
    exit(1);
}

echo "SMS delivery lifecycle regression checks passed.\n";
