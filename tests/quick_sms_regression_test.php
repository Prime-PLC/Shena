<?php

$root = dirname(__DIR__);
$controller = file_get_contents($root . '/app/controllers/BulkSmsController.php');
$service = file_get_contents($root . '/app/services/BulkSmsService.php');
$router = file_get_contents($root . '/app/core/Router.php');
$view = file_get_contents($root . '/resources/views/admin/sms-campaigns.php');

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

$assertNotContains($controller, 'findById($recipientId)', 'quick SMS must not call a missing Member::findById method');
$assertContains($controller, 'getMemberById($recipientId)', 'quick SMS individual recipients should use the existing member lookup');
$assertContains($controller, 'catch (Throwable $e)', 'quick SMS should catch fatal Errors and return JSON');
$assertContains($controller, 'personalizeQuickSmsMessage', 'quick SMS should replace supported member placeholders before sending');
$assertContains($controller, "'{member_name}'", 'quick SMS placeholder support should include member name');
$assertContains($controller, "'{amount_due}'", 'quick SMS placeholder support should include amount due');
$assertContains($controller, 'queueQuickSms', 'quick SMS should create tracked queue entries before delivery.');
$assertContains($controller, 'processQueueByIds', 'quick SMS should submit only the newly queued entries.');
$assertContains($controller, 'SELECT DISTINCT u.id AS user_id', 'quick SMS group/all member queries should not return duplicate member rows.');
$assertNotContains($controller, 'Delivery confirmation is not available for quick SMS', 'quick SMS should be delivery-trackable.');
$assertNotContains($controller, 'HostPinnacle', 'quick SMS controller responses should not expose provider branding.');
$assertContains($service, 'queueQuickSms', 'bulk SMS service should expose quick SMS queue persistence.');
$assertContains($service, 'processQueueByIds', 'bulk SMS service should process selected quick SMS queue rows.');
$assertContains($service, '$seenPhones = []', 'quick SMS queueing should track normalized phone numbers.');
$assertContains($service, 'isset($seenPhones[$phone])', 'quick SMS queueing should skip duplicate phone numbers before insert.');
$assertContains($service, 'validatePhoneNumber($phone)', 'quick SMS queueing should refuse invalid phone numbers before insert.');
$assertContains($service, 'getQueueItems($filters', 'bulk SMS service should support filtered queue listing.');
$assertContains($service, 'getQueueItemsCount', 'bulk SMS service should support queue pagination counts.');
$assertContains($router, '/admin/communications/delete-campaign', 'admin communications should expose campaign deletion.');

$assertContains($view, 'parseJsonResponse(response)', 'quick SMS frontend should handle non-JSON server errors safely');
$assertContains($view, 'response.ok', 'quick SMS frontend should check HTTP failures before showing success');
$assertContains($view, 'quick-member-search', 'quick SMS individual recipient should use searchable member lookup');
$assertContains($view, '/admin/api/members?search=', 'quick SMS member lookup should query by search term');
$assertContains($view, 'quick-sms-results', 'quick SMS search should show live result cards while typing.');
$assertContains($view, 'selectQuickSmsMember', 'quick SMS should select a member from visible live results.');
$assertContains($view, 'data-sms-tab="quick"', 'SMS page should expose a Quick SMS tracking tab.');
$assertContains($view, 'deleteCampaign(', 'SMS campaigns should expose a delete action with confirmation.');
$assertContains($view, 'deleteQueueItem(', 'quick SMS queue entries should expose delete action with confirmation.');
$assertNotContains($view, 'HostPinnacle', 'SMS campaign UI should not expose provider branding.');

$api = file_get_contents($root . '/app/controllers/AdminApiController.php');
$assertContains($api, "'id_number'", 'member search API should expose national ID for admin selection context');
$assertContains($api, "'member_name'", 'member search API should expose display name for admin selection context');

echo "Quick SMS regression checks passed.\n";
