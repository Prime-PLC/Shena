<?php

$root = dirname(__DIR__);
$controller = file_get_contents($root . '/app/controllers/BulkSmsController.php');
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

$assertContains($view, 'parseJsonResponse(response)', 'quick SMS frontend should handle non-JSON server errors safely');
$assertContains($view, 'response.ok', 'quick SMS frontend should check HTTP failures before showing success');
$assertContains($view, 'quick-member-search', 'quick SMS individual recipient should use searchable member lookup');
$assertContains($view, '/admin/api/members?search=', 'quick SMS member lookup should query by search term');

$api = file_get_contents($root . '/app/controllers/AdminApiController.php');
$assertContains($api, "'id_number'", 'member search API should expose national ID for admin selection context');
$assertContains($api, "'member_name'", 'member search API should expose display name for admin selection context');

echo "Quick SMS regression checks passed.\n";
