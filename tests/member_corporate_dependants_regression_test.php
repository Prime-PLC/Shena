<?php

$root = dirname(__DIR__);

require_once $root . '/app/services/MembershipPricingService.php';

$failed = false;

$assertContains = function (string $contents, string $needle, string $message) use (&$failed): void {
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, $message . "\nMissing: {$needle}\n");
        $failed = true;
    }
};

$assertNotContains = function (string $contents, string $needle, string $message) use (&$failed): void {
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, $message . "\nUnexpected: {$needle}\n");
        $failed = true;
    }
};

$assertSame = function ($expected, $actual, string $message) use (&$failed): void {
    if ($expected !== $actual) {
        fwrite(STDERR, $message . " Expected {$expected}, got {$actual}\n");
        $failed = true;
    }
};

$assertBefore = function (string $contents, string $firstNeedle, string $secondNeedle, string $message) use (&$failed): void {
    $first = strpos($contents, $firstNeedle);
    $second = strpos($contents, $secondNeedle);
    if ($first === false || $second === false || $first >= $second) {
        fwrite(STDERR, $message . "\nExpected {$firstNeedle} before {$secondNeedle}\n");
        $failed = true;
    }
};

$packages = require $root . '/config/packages.php';

$assertSame(200, MembershipPricingService::resolveSelectedPackageAmount('couple_children_below_70', $packages), 'Selected couple and children package should keep KES 200.');
$assertSame(350, MembershipPricingService::resolveSelectedPackageAmount('couple_children_parents_70_80', $packages), 'Selected 70-80 package should keep KES 350.');
$assertSame(500, MembershipPricingService::resolveSelectedPackageAmount('executive_above_70', $packages), 'Selected executive above 70 package should keep KES 500.');
$assertSame(10, $packages['couple_children_below_70']['max_children'] ?? 0, 'Couple and children family coverage should allow multiple child dependants.');

$corporateItems = [
    ['package_key' => 'executive_above_70'],
    ['package_key' => 'couple_below_70'],
];
$summary = MembershipPricingService::calculateAccountMonthlyContribution('couple_children_below_70', $corporateItems, $packages);
$assertSame(200, $summary['main_amount'], 'Main package amount should come from selected package.');
$assertSame(650, $summary['corporate_total'], 'Corporate total should sum selected corporate package amounts.');
$assertSame(850, $summary['total_amount'], 'Account payable amount should be main package plus corporate line items.');

$migration = file_get_contents($root . '/database/migrations/015_member_corporate_members.sql');
$assertContains($migration, 'CREATE TABLE IF NOT EXISTS member_corporate_members', 'Corporate members migration should create line-item table.');
$assertContains($migration, 'package_key', 'Corporate members migration should store selected package key.');
$assertContains($migration, 'monthly_contribution', 'Corporate members migration should store selected monthly amount.');
$assertNotContains($migration, 'USE ', 'Migration should not force a database name.');

$model = file_get_contents($root . '/app/models/MemberCorporateMember.php');
$assertContains($model, "protected \$table = 'member_corporate_members'", 'Corporate member model should target the line-item table.');
$assertContains($model, 'replaceForMember', 'Corporate member model should support replacing line items during member save.');
$assertContains($model, 'getActiveForMember', 'Corporate member model should load active line items for display and totals.');

$adminController = file_get_contents($root . '/app/controllers/AdminController.php');
$assertContains($adminController, 'MemberCorporateMember', 'Admin controller should use corporate member model.');
$assertContains($adminController, 'parseCorporateMembersFromPost', 'Admin controller should parse corporate member line items.');
$assertContains($adminController, 'calculateAccountMonthlyContribution', 'Admin saves should use account-level package totals.');
$assertContains($adminController, 'addMemberDependant', 'Admin controller should expose strict dependant addition.');
$assertContains($adminController, 'updateMemberDependant', 'Admin controller should expose dependant edit action.');
$assertContains($adminController, 'deleteMemberDependant', 'Admin controller should expose dependant delete action.');
$assertContains($adminController, 'deleteMemberCorporateMember', 'Admin controller should expose corporate member delete action.');
$assertContains($adminController, 'evaluateDependentCoverageForAddition', 'Admin dependant addition should enforce plan coverage.');
$assertContains($adminController, 'activation_override', 'Admin activation should require an explicit override flag when system restrictions are present.');
$assertContains($adminController, 'activation_restrictions', 'Full member profile should receive activation restriction reasons.');
$assertContains($adminController, 'getMemberActivationRestrictions', 'Admin activation should evaluate fee and status restrictions before activating.');
$assertContains($adminController, 'dependant_override', 'Admin dependant addition should support explicit override of system restrictions.');
$assertContains($adminController, 'validateDependentAddition', 'Admin dependant addition should use the central dependent policy.');

$memberModel = file_get_contents($root . '/app/models/Member.php');
$assertContains($memberModel, 'getPlanCoverageLimitsForMember', 'Member coverage checks should use concrete package limits before falling back to normalized tier limits.');
$assertContains($memberModel, 'extractDependentCoverageLimits($package)', 'Member coverage checks should honor max_children from the selected package.');
$assertContains($memberModel, "\$default['children'] = 10;", 'Generic family coverage should support multiple children, not spouse-only coverage.');
$assertContains($memberModel, 'validateDependentAddition', 'Dependent add policy should be centralized for member, agent, and admin portals.');
$assertContains($memberModel, "'requires_override'", 'Central dependent policy should signal admin-only override requirements.');
$assertContains($memberModel, 'Children must be below 18 years', 'Central dependent policy should enforce child age consistently.');

$router = file_get_contents($root . '/app/core/Router.php');
$assertContains($router, '/admin/members/{id}/dependants/add', 'Router should expose admin dependant add endpoint.');
$assertContains($router, '/admin/members/{id}/dependants/update', 'Router should expose admin dependant update endpoint.');
$assertContains($router, '/admin/members/{id}/dependants/delete', 'Router should expose admin dependant delete endpoint.');
$assertContains($router, '/admin/members/{id}/corporate-members/delete', 'Router should expose admin corporate member delete endpoint.');

$membersView = file_get_contents($root . '/resources/views/admin/members.php');
$assertNotContains($membersView, 'name="corporate_couple_count"', 'Member management modal should not rely on corporate count dropdown.');
$assertContains($membersView, '/admin/members/view/', 'Member management list should open the full profile view.');
$assertNotContains($membersView, 'openMemberManageModal', 'Member management list should not use the quick manage modal.');

$memberDetailsView = file_get_contents($root . '/resources/views/admin/member-details.php');
$assertContains($memberDetailsView, 'Manage Member Profile', 'Full profile view should be the admin management surface.');
$assertContains($memberDetailsView, 'action="/admin/members/update/', 'Full profile view should save member edits.');
$assertContains($memberDetailsView, 'Corporate Members', 'Full profile view should show corporate member section.');
$assertContains($memberDetailsView, 'Dependants', 'Full profile view should show dependant section.');
$assertContains($memberDetailsView, 'action="/admin/members/', 'Full profile view should include admin dependant save action.');
$assertContains($memberDetailsView, '/admin/members', 'Full profile view should include a clear return path to member management.');
$assertContains($memberDetailsView, 'id="memberEditPanel"', 'Full profile view should keep member edit form behind an explicit panel.');
$assertContains($memberDetailsView, 'id="dependantAddPanel"', 'Full profile view should keep dependant addition behind an explicit panel.');
$assertContains($memberDetailsView, 'id="corporateManagePanel"', 'Full profile view should keep corporate editing behind an explicit panel.');
$assertContains($memberDetailsView, 'id="activationOverride"', 'Activation form should include a hidden override flag.');
$assertContains($memberDetailsView, 'activationRestrictions', 'Activation confirmation should know when policy restrictions exist.');
$assertContains($memberDetailsView, 'System checks require attention', 'Activation card should explain blocked system requirements.');
$assertContains($memberDetailsView, 'profile-tabs', 'Full profile view should use operational tabs.');
$assertContains($memberDetailsView, 'data-profile-tab="dependants"', 'Full profile view should provide a Dependants tab.');
$assertContains($memberDetailsView, 'id="dependantOverride"', 'Admin dependant form should include a hidden override flag.');
$assertContains($memberDetailsView, 'dependantRestrictions', 'Admin dependant confirmation should know when restrictions require override.');
$assertContains($memberDetailsView, 'openDependantEdit', 'Dependant cards should expose an edit action.');
$assertContains($memberDetailsView, 'deleteDependant', 'Dependant cards should expose a delete action.');
$assertContains($memberDetailsView, 'editCorporateMember', 'Corporate cards should expose an edit action.');
$assertContains($memberDetailsView, 'deleteCorporateMember', 'Corporate cards should expose a delete action.');
$assertContains($memberDetailsView, 'updateDependantDobConstraints', 'Admin dependant date picker should react to relationship selection.');
$assertContains($memberDetailsView, "if (relation === 'child')", 'Child dependant selection should loosen the adult-only DOB constraint.');
$assertContains($memberDetailsView, 'Children must be below 18 years old.', 'Child dependant DOB guidance should allow ages below 18.');
$assertContains($memberDetailsView, 'Adult dependants must be 18 years or older.', 'Adult dependant DOB guidance should keep the 18+ constraint.');
$assertBefore($memberDetailsView, 'Member Overview', 'id="adminMemberProfileForm"', 'Member overview should appear before the prefilled edit form.');
$assertBefore($memberDetailsView, 'Payment History', 'Collect Payment', 'Payment actions should live with payment history.');
$assertNotContains($memberDetailsView, 'Archive', 'Full profile view should not expose archive actions.');
$assertNotContains($memberDetailsView, 'linear-gradient', 'Full profile view should not use gradient styling.');
$assertNotContains($memberDetailsView, '<i class="fas', 'Full profile view should avoid old icon-heavy controls.');

$beneficiariesView = file_get_contents($root . '/resources/views/member/beneficiaries.php');
$assertContains($beneficiariesView, "'value' => 'child'", 'Beneficiary add form should submit canonical child relationship value.');
$assertContains($beneficiariesView, "'value' => 'father_in_law'", 'Beneficiary add form should submit canonical in-law relationship value.');

$memberController = file_get_contents($root . '/app/controllers/MemberController.php');
$assertContains($memberController, 'normalizeBeneficiaryRelationship', 'Member controller should normalize beneficiary relationship values before save.');
$assertContains($memberController, 'validateDependentAddition', 'Member dependant addition should use the central dependent policy.');

$paymentModel = file_get_contents($root . '/app/models/Payment.php');
$assertContains($paymentModel, 'hasPaidReactivationFee', 'Payment model should expose a central reactivation fee check for admin activation policy.');

exit($failed ? 1 : 0);
