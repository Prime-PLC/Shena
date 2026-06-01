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
$adminController = file_get_contents($root . '/app/controllers/AdminController.php');
$memberModel = file_get_contents($root . '/app/models/Member.php');
$membersView = file_get_contents($root . '/resources/views/admin/members.php');
$smsController = file_get_contents($root . '/app/controllers/BulkSmsController.php');
$emailController = file_get_contents($root . '/app/controllers/BulkEmailController.php');
$migration = file_get_contents($root . '/database/migrations/014_member_archive_support.sql');

$assertContains($router, "/admin/members/archived", 'archived members should have an admin route');
$assertContains($router, "/admin/members/delete", 'member delete/archive action should have an admin route');
$assertContains($router, "/admin/claims/submit", 'admin claim submission should have an admin route');
$assertContains($router, "/admin/api/members/{id}/beneficiaries", 'admin claim form should load selected member beneficiaries');
$assertContains($router, "/admin/email-campaigns/edit", 'email campaigns should support edit route');

$assertContains($memberModel, 'archiveMember', 'member model should archive members with history');
$assertContains($memberModel, 'canHardDelete', 'member model should check payments/claims before hard delete');
$assertContains($memberModel, 'getArchivedMembersWithDetails', 'member model should expose archived members list');
$assertContains($memberModel, 'archived_at IS NULL', 'normal member lists should exclude archived members');

$assertContains($migration, 'archived_at', 'migration should add archived timestamp');
$assertContains($migration, 'archive_reason', 'migration should add archive reason');

$assertContains($adminController, 'deleteOrArchiveMember', 'admin should handle member delete/archive');
$assertContains($adminController, 'archivedMembers', 'admin should render archived member view');
$assertContains($adminController, 'submitClaimForMember', 'admin should submit claims for members');
$assertContains($adminController, 'sendClaimAcknowledgementSms', 'claim submission should trigger acknowledgement SMS');
$assertContains($adminController, 'SHENA has received your claim', 'acknowledgement SMS should be consoling and explicit');
$assertContains($adminController, 'sendClaimLifecycleSms', 'claim lifecycle changes should notify members by SMS');

$assertContains($membersView, 'Monthly payable amount', 'member view/manage should show monthly payable amount');
$assertNotContains($membersView, 'Daily payable amount', 'member UI must not derive or display a daily amount');
$assertContains($membersView, 'memberDeleteConfirmModal', 'manage modal should provide strict delete/archive confirmation');
$assertContains($membersView, 'confirm_member_number', 'delete/archive must require member number confirmation');
$assertContains($membersView, '/admin/members/archived', 'archived members should be accessible from member management tabs');

$archivedViewPath = $root . '/resources/views/admin/members-archived.php';
if (!file_exists($archivedViewPath)) {
    fwrite(STDERR, "Archived members view is missing\n");
    exit(1);
}
$archivedView = file_get_contents($archivedViewPath);
$assertContains($archivedView, 'Archived Members', 'archived view should be clearly labelled');
$assertContains($archivedView, 'archive_reason', 'archived view should show archive reason');

$assertContains($smsController, 'editCampaign', 'SMS controller should support campaign editing');
$assertNotContains($smsController, 'Edit feature coming soon', 'SMS edit should not be a stub');
$assertContains($smsController, 'bulkMessagesHasColumn', 'SMS edit should tolerate production tables without optional timestamp columns');
$assertNotContains($smsController, "status = CASE WHEN ? IS NULL THEN 'draft' ELSE 'scheduled' END, updated_at = NOW()", 'SMS edit must not hard-code updated_at on production schemas that lack it');
$assertContains($emailController, 'editCampaign', 'Email controller should support campaign editing');
$assertContains($emailController, 'bulkMessagesHasColumn', 'Email edit should tolerate production tables without optional timestamp columns');

$smsView = file_get_contents($root . '/resources/views/admin/sms-campaigns.php');
$emailView = file_get_contents($root . '/resources/views/admin/email-campaigns.php');
$assertContains($smsView, 'editCampaignModal', 'SMS scheduled/draft campaigns should edit through an admin modal');
$assertContains($smsView, 'edit-target-audience', 'SMS edit modal should let admins update campaign audience');
$assertContains($smsView, 'edit-custom-filters-panel', 'SMS edit modal should expose custom audience filters');
$assertContains($smsView, 'filter_joined_before', 'SMS edit should preserve joined-before custom filter');
$assertContains($emailView, 'editCampaignModal', 'Email scheduled/draft campaigns should edit through an admin modal');
$assertContains($emailView, 'edit-target-audience', 'Email edit modal should let admins update campaign audience');
$assertContains($emailView, 'edit-custom-filters-panel', 'Email edit modal should expose custom audience filters');
$assertNotContains($smsView, 'prompt(', 'SMS campaign editing should not use browser prompt dialogs');
$assertNotContains($emailView, 'prompt(', 'Email campaign editing should not use browser prompt dialogs');

$claimsView = file_get_contents($root . '/resources/views/admin/claims.php');
$apiController = file_get_contents($root . '/app/controllers/AdminApiController.php');
$assertContains($claimsView, 'enctype="multipart/form-data"', 'admin claim form should accept the same claim documents as member portal');
$assertContains($claimsView, 'adminClaimBeneficiaryId', 'admin claim form should include beneficiary selection');
$assertContains($claimsView, 'request_cash_alternative', 'admin claim form should use the same cash alternative field as member portal');
$assertContains($claimsView, 'adminClaimCashAlternativeReasonField', 'admin claim form should toggle cash alternative reason');
$assertContains($claimsView, '/admin/api/members/', 'admin claim form should fetch selected member beneficiaries');
$assertContains($apiController, 'memberBeneficiaries', 'admin API should provide member beneficiaries for claim submission');
$assertContains($adminController, 'processAdminClaimDocumentUploads', 'admin claim submission should process required documents like member claims');

echo "Admin member, claim, and campaign regression checks passed.\n";
