<?php

$root = dirname(__DIR__);

$failed = false;

$read = function (string $path) use ($root): string {
    $fullPath = $root . '/' . $path;
    if (!file_exists($fullPath)) {
        fwrite(STDERR, "{$path} does not exist\n");
        return '';
    }

    return file_get_contents($fullPath);
};

$assertContains = function (string $contents, string $needle, string $message) use (&$failed): void {
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "{$message}\nMissing: {$needle}\n");
        $failed = true;
    }
};

$assertNotContains = function (string $contents, string $needle, string $message) use (&$failed): void {
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, "{$message}\nUnexpected: {$needle}\n");
        $failed = true;
    }
};

$adminController = $read('app/controllers/AdminController.php');
$memberController = $read('app/controllers/MemberController.php');
$agentDashboardController = $read('app/controllers/AgentDashboardController.php');
$agentController = $read('app/controllers/AgentController.php');
$bulkSmsController = $read('app/controllers/BulkSmsController.php');
$claimModel = $read('app/models/Claim.php');
$reconciliationService = $read('app/services/PaymentReconciliationService.php');
$router = $read('app/core/Router.php');
$paymentsView = $read('resources/views/admin/payments.php');
$upgradeView = $read('resources/views/member/upgrade.php');
$beneficiariesView = $read('resources/views/member/beneficiaries.php');
$agentMemberDetailsView = $read('resources/views/agent/member-details.php');
$agentRegisterView = $read('resources/views/agent/register-member.php');

$assertContains($adminController, 'getAdminPaymentSummary', 'Admin payments should pass real summary stats to the all-payments view.');
$assertContains($adminController, "'totalPayments' =>", 'Admin payments should pass totalPayments to the view.');
$assertContains($adminController, "'monthlyPayments' =>", 'Admin payments should pass monthlyPayments to the view.');
$assertContains($adminController, "'pendingReconciliation' =>", 'Admin payments should pass pendingReconciliation to the view.');
$assertContains($paymentsView, '$paymentSummary', 'Payments view should use a central payment summary payload.');

$assertContains($memberController, "'submitted', 'under_review', 'approved', 'services_arranged'", 'Member dashboard should count current claim statuses as active.');
$assertContains($claimModel, "status IN ('submitted', 'under_review'", 'Claim statistics should use current submitted/under-review statuses.');
$assertNotContains($claimModel, "COUNT(CASE WHEN status = 'pending' THEN 1 END)", 'Claim statistics should not rely on legacy pending status only.');
$assertContains($bulkSmsController, "m.package_key LIKE 'couple_%'", 'SMS member stats should count current package keys, not only legacy package labels.');
$assertContains($bulkSmsController, "m.package_key LIKE 'executive_%'", 'SMS member stats should include current executive package keys.');

$assertContains($adminController, 'friendlyErrorMessage', 'Admin controller should centralize user-safe error messages.');
$assertContains($memberController, 'friendlyErrorMessage', 'Member controller should centralize user-safe error messages.');
$assertContains($agentDashboardController, 'friendlyErrorMessage', 'Agent dashboard controller should centralize user-safe error messages.');
$assertContains($reconciliationService, 'A payment verification service error occurred.', 'Reconciliation service should not return raw exception text to users.');
$assertNotContains($memberController, "'Failed to add beneficiary: ' . \$e->getMessage()", 'Member beneficiary add should not expose raw exceptions.');
$assertNotContains($agentDashboardController, "'Failed to add dependent: ' . \$e->getMessage()", 'Agent dependent add should not expose raw exceptions.');
$assertNotContains($adminController, "'Failed to update member: ' . \$e->getMessage()", 'Admin member update should not expose raw exceptions.');

$assertContains($agentDashboardController, 'MemberCorporateMember', 'Agent registration should persist corporate line items.');
$assertContains($agentDashboardController, 'parseCorporateMembersFromPost', 'Agent registration should parse corporate package line items.');
$assertContains($agentDashboardController, 'calculateAccountMonthlyContribution', 'Agent registration should use account pricing for corporate members.');
$assertNotContains($agentRegisterView, 'name="corporate_couple_count"', 'Agent registration should not use corporate count dropdown.');

$assertNotContains($beneficiariesView, 'name="percentage"', 'Member dependant/beneficiary form should not force percentage allocation.');
$assertNotContains($agentMemberDetailsView, 'name="percentage"', 'Agent dependent form should not force percentage allocation.');
$assertNotContains($memberController, 'Total beneficiary percentage cannot exceed 100%.', 'Member dependant add/update should not block plan-covered dependants by percentage.');
$assertNotContains($agentDashboardController, 'Total beneficiary percentage cannot exceed 100%.', 'Agent dependant add should not block plan-covered dependants by percentage.');

$assertContains($agentMemberDetailsView, "name=\"csrf_token\"", 'Agent payment assistance must include CSRF in AJAX payload.');
$assertContains($agentDashboardController, '$this->validateCsrf();', 'Agent payment assistance endpoint must validate CSRF.');
$assertNotContains($agentMemberDetailsView, 'id="paymentAssistModal"', 'Agent member details should not keep duplicate payment assistance modal.');

$assertContains($agentController, "\$this->agentModel->getAllAgents(['search' => \$search, 'status' => \$statusFilter], null, 0)", 'Agent CSV export should call getAllAgents without a snapshot limit.');
$assertContains($router, '$this->registerAgentRoutes();', 'Router should group agent routes once.');
$assertNotContains($router, "\$this->addRoute('GET', '/agent/dashboard', 'AgentDashboardController@dashboard');\r\n        \$this->addRoute('GET', '/agent/payouts'", 'Router should not duplicate agent dashboard route block.');

$assertContains($upgradeView, 'upgrade-table-shell', 'Member upgrade UI should use table-first in-app layout.');
$assertNotContains($upgradeView, 'fa-crown', 'Member upgrade UI should not use crown-heavy premium badges.');
$assertNotContains($upgradeView, 'premium-badge-new', 'Member upgrade UI should not use old premium badge UI.');
$assertNotContains($upgradeView, 'linear-gradient(145deg', 'Member upgrade UI should remove loud plan gradients.');

$assertContains($beneficiariesView, 'dependant-shell', 'Beneficiaries/dependants page should use standardized in-app shell.');
$assertNotContains($beneficiariesView, '#EC4899', 'Beneficiary avatars should not use pink gender coloring.');
$assertNotContains($beneficiariesView, '#3B82F6', 'Beneficiary avatars should not use blue gender coloring.');
$assertNotContains($beneficiariesView, 'upgrade-cta-card', 'Beneficiary page should not use crown-heavy upgrade CTA card.');

exit($failed ? 1 : 0);
