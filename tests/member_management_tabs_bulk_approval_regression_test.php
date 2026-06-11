<?php

$root = dirname(__DIR__);

$membersView = file_get_contents($root . '/resources/views/admin/members.php');
$memberModel = file_get_contents($root . '/app/models/Member.php');
$adminController = file_get_contents($root . '/app/controllers/AdminController.php');
$reconciliationService = file_get_contents($root . '/app/services/PaymentReconciliationService.php');

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

$assertContains($membersView, '$buildMemberFilterUrl', 'member tabs should generate server-side filter URLs');
$assertContains($membersView, '$buildMemberFilterUrl(\'pending_approval\')', 'pending approval tab should refilter the table');
$assertContains($membersView, '$buildMemberFilterUrl(\'active\')', 'active tab should refilter the table');
$assertContains($membersView, '$buildMemberFilterUrl(\'suspended\')', 'suspended tab should refilter the table');
$assertContains($membersView, '$buildMemberFilterUrl(\'grace_period\')', 'grace tab should refilter the table');
$assertNotContains($membersView, 'onclick="switchTab(\'active\')"', 'active member tab must not be a client-only panel switch');
$assertContains($membersView, 'name="status"', 'filter form should still submit the status filter');
$assertContains($membersView, 'value="pending_approval"', 'filter form should expose pending approval status');
$assertContains($membersView, 'memberSearchInput?.addEventListener(\'input\'', 'member search should auto-submit while typing.');
$assertContains($membersView, 'memberSearchForm.submit()', 'member search auto-submit should use the existing filter form.');

$assertContains($memberModel, '$status === \'pending_approval\'', 'member query should understand pending approval as inactive or pending');
$assertContains($memberModel, "m.status IN ('inactive', 'pending')", 'pending approval filter should include both inactive and pending members');

$assertContains($adminController, 'hasPaidRegistrationFee($memberId)', 'bulk approval should use the central registration fee check');
$assertContains($adminController, 'activateMemberAfterRegistrationPayment($memberId)', 'bulk approval should use the central activation side effects');
$assertContains($adminController, 'override_system_restrictions', 'bulk activation APIs should only bypass policy checks when the admin explicitly sends the override flag');
$assertContains($adminController, 'getMemberActivationRestrictions', 'bulk activation APIs should report the same activation restrictions as the profile view');
$assertNotContains($adminController, '$registrationFeeRequired = defined(\'REGISTRATION_FEE\')', 'bulk approval should not duplicate registration fee summing logic');

$assertContains($reconciliationService, "payment_type = :payment_type", 'receipt verification should normalize existing payments to the requested type');
$assertContains($reconciliationService, 'confirmPayment($existingPayment[\'id\'], $transId)', 'receipt verification should re-run payment side effects for already completed receipts');

echo "Member management tab filtering and bulk approval regression checks passed.\n";
