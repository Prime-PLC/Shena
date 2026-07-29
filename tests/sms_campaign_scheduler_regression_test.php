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
$assertContains($service, 'claimCampaignForSending', 'campaign senders should atomically claim draft/scheduled/paused campaigns before sending');
$assertContains($service, 'claimPendingRecipientsForCampaign', 'campaign recipients should be claimed before provider submission to prevent duplicate sends');
$assertContains($service, 'resumePausedCampaignForManualSend', 'manual Send Now should explicitly resume paused campaigns instead of scheduler processing doing it implicitly');
$assertContains($service, "UPDATE bulk_messages SET status = 'sending' WHERE id = ? AND status = 'paused'", 'resuming a paused campaign should not turn it into a draft and refresh/resend all recipients');
$assertNotContains($service, "UPDATE bulk_messages SET status = 'draft' WHERE id = ? AND status = 'paused'", 'resuming a paused campaign must not trigger draft recipient refresh');
$assertContains($service, "Campaign is paused", 'scheduled/batch processing should stop when a campaign has been paused');
$assertContains($service, "status IN ('draft', 'scheduled')", 'automatic campaign claiming should not auto-resume paused campaigns');
$assertContains($service, 'findRecentDuplicateCampaign', 'campaign creation should reuse a recent identical campaign instead of creating a duplicate row');
$assertContains($service, 'created_at >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)', 'duplicate campaign guard should be scoped to recent accidental resubmits');
$assertContains($service, 'normalizeCampaignTargetAudience', 'campaign creation and refresh should normalize target audience safely');
$assertContains($service, 'Campaign target audience is required', 'campaign creation should reject blank audiences instead of later sending to all members');
$assertContains($service, 'Campaign target audience is missing; refusing to send', 'campaign sending should stop existing bad campaign rows with blank audiences');
$assertContains($service, 'Campaign target audience is missing; refusing to refresh recipients', 'scheduled sending should fail closed when a campaign has a blank audience');
$assertNotContains($service, "\$targetAudience ?: 'all_members'", 'blank SMS campaign audiences must not fall back to all members');
$assertContains($service, "SET status = 'processing'", 'pending campaign recipients should move to processing before provider submission');
$assertContains($service, "AND status = 'pending'", 'recipient claim should only reserve still-pending rows');
$assertContains($service, "bmr.status = 'processing'", 'campaign sending should submit only recipients claimed by this worker');
$assertContains($service, "\$this->updateRecipientStatus(\$recipient['id'], 'pending')", 'quiet-hours recipients should be released for a later run instead of remaining processing-locked');
$assertContains($service, "'deferred_count' => \$deferredCount", 'campaign batching should report recipients deferred by quiet hours');
$assertContains($service, "(int) (\$result['deferred_count'] ?? 0) > 0", 'campaign batching should stop after deferring quiet-hours recipients to avoid reclaiming them immediately');
$assertContains($service, "status IN ('pending', 'processing')", 'campaign pending totals should include recipients currently claimed by a worker');
$assertContains($service, "status IN ('draft', 'scheduled', 'paused')", 'initial campaign claim should only start campaigns that have not already been started');

$assertContains($controller, 'processScheduledCampaigns', 'controller should expose a scheduled campaign processor');
$assertContains($controller, 'downloadDeliveryReport', 'controller should expose downloadable delivery reports');
$assertContains($controller, 'resendPendingFailed', 'controller should expose resend for pending/failed recipients');
$assertContains($controller, "\$input['target_audience'] ?? ''", 'SMS campaign edit should not silently default missing audience to all members');
$assertContains($controller, "status = 'paused'", 'controller should be able to pause actively sending campaigns');
$assertContains($controller, 'resumePausedCampaignForManualSend', 'manual send endpoint should explicitly resume paused campaigns');
$assertNotContains($controller, "UPDATE bulk_messages SET status = 'paused', updated_at = NOW()", 'pause should not hard-code optional updated_at column');
$assertNotContains($controller, "UPDATE bulk_messages SET scheduled_at = ?, updated_at = NOW()", 'reschedule should not hard-code optional updated_at column');

$assertContains($router, '/admin/communications/process-scheduled-campaigns', 'admin route should process due scheduled campaigns');
$assertContains($router, '/admin/communications/campaign/{id}/delivery-report', 'admin route should download campaign delivery reports');
$assertContains($router, '/admin/communications/campaign/{id}/resend-pending-failed', 'admin route should resend pending/failed recipients');

$assertContains($listView, 'processScheduledCampaigns', 'SMS campaign page should provide a processor action');
$assertContains($listView, 'downloadDeliveryReport', 'SMS campaign page should provide report download action');
$assertContains($listView, 'resendPendingFailed', 'SMS campaign page should provide resend action');
$assertContains($listView, 'Pause Sending', 'SMS campaign list should expose a pause action for actively sending campaigns');
$assertContains($listView, "\$campaign['status'] === 'sending'", 'SMS campaign list pause action should only show for actively sending campaigns');
$assertContains($detailsView, 'delivery-report', 'campaign details should link to delivery report download');
$assertContains($detailsView, 'resendPendingFailed', 'campaign details should allow resending pending/failed recipients');
$assertContains($detailsView, 'Pause Sending', 'campaign details should expose a pause action for actively sending campaigns');
$assertContains($detailsView, "\$campaign['status'] ?? '') === 'sending'", 'campaign details pause action should only show for actively sending campaigns');

$cronPath = $root . '/tools/process_sms_campaigns.php';
if (!file_exists($cronPath)) {
    fwrite(STDERR, "Assertion failed: cron-safe SMS campaign processor script is missing\n");
    exit(1);
}

$cronScript = file_get_contents($cronPath);
$assertContains($cronScript, 'processDueCampaigns', 'cron script should process due campaigns');
$assertContains($cronScript, 'processQueue', 'cron script should still process standalone SMS queue items');

$migrationPath = $root . '/database/migrations/016_sms_campaign_processing_locks.sql';
if (!file_exists($migrationPath)) {
    fwrite(STDERR, "Assertion failed: SMS campaign processing-lock migration is missing\n");
    exit(1);
}

$lockMigration = file_get_contents($migrationPath);
$assertContains($lockMigration, "'processing'", 'recipient status enum should include processing for send locks');

echo "SMS campaign scheduler regression checks passed.\n";
