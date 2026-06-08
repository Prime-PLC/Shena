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

$router = file_get_contents($root . '/app/core/Router.php');
$controller = file_get_contents($root . '/app/controllers/BulkSmsController.php');
$service = file_get_contents($root . '/app/services/BulkSmsService.php');
$listView = file_get_contents($root . '/resources/views/admin/sms-campaigns.php');
$detailsView = file_get_contents($root . '/resources/views/admin/campaign-details.php');

$assertContains($service, 'processDueCampaigns', 'scheduled SMS campaigns should be processed when due');
$assertContains($service, 'sendCampaignUntilComplete', 'SMS sending should continue beyond the first 50-recipient batch');
$assertContains($service, 'resetRecipientsForResend', 'failed/pending campaign recipients should be resettable for resend');
$assertContains($service, 'getDueCampaigns', 'scheduled and in-progress campaigns should be picked up by the processor');
$assertContains($service, "bm.status = 'scheduled' AND bm.scheduled_at <= NOW()", 'due scheduled campaigns must be selected by scheduled time');

$assertContains($controller, 'processScheduledCampaigns', 'controller should expose a scheduled campaign processor');
$assertContains($controller, 'downloadDeliveryReport', 'controller should expose downloadable delivery reports');
$assertContains($controller, 'resendPendingFailed', 'controller should expose resend for pending/failed recipients');
$assertNotContains($controller, "UPDATE bulk_messages SET status = 'paused', updated_at = NOW()", 'pause should not hard-code optional updated_at column');
$assertNotContains($controller, "UPDATE bulk_messages SET scheduled_at = ?, updated_at = NOW()", 'reschedule should not hard-code optional updated_at column');

$assertContains($router, '/admin/communications/process-scheduled-campaigns', 'admin route should process due scheduled campaigns');
$assertContains($router, '/admin/communications/campaign/{id}/delivery-report', 'admin route should download campaign delivery reports');
$assertContains($router, '/admin/communications/campaign/{id}/resend-pending-failed', 'admin route should resend pending/failed recipients');

$assertContains($listView, 'processScheduledCampaigns', 'SMS campaign page should provide a processor action');
$assertContains($listView, 'downloadDeliveryReport', 'SMS campaign page should provide report download action');
$assertContains($listView, 'resendPendingFailed', 'SMS campaign page should provide resend action');
$assertContains($detailsView, 'delivery-report', 'campaign details should link to delivery report download');
$assertContains($detailsView, 'resendPendingFailed', 'campaign details should allow resending pending/failed recipients');

$cronPath = $root . '/tools/process_sms_campaigns.php';
if (!file_exists($cronPath)) {
    fwrite(STDERR, "Assertion failed: cron-safe SMS campaign processor script is missing\n");
    exit(1);
}

$cronScript = file_get_contents($cronPath);
$assertContains($cronScript, 'processDueCampaigns', 'cron script should process due campaigns');
$assertContains($cronScript, 'processQueue', 'cron script should still process standalone SMS queue items');

echo "SMS campaign scheduler regression checks passed.\n";
