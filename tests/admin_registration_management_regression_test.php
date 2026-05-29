<?php

$root = dirname(__DIR__);

$checks = [
    [
        'file' => 'app/controllers/AdminController.php',
        'mustContain' => [
            "'corporate_couple_count' => \$corporateCoupleCount",
            "\$memberUserData = [",
            "\$memberRecordData = [",
            "\$this->userModel->update(\$member['user_id'], \$memberUserData)",
            "\$this->memberModel->update(\$id, \$memberRecordData)",
            'notifyMemberManagementChange',
            "\$returnTo = \$_POST['return_to'] ?? '/admin/members'",
        ],
        'mustNotContain' => [
            "'sub_county' => \$_POST['sub_county']",
            "'county' => \$_POST['county']",
        ],
    ],
    [
        'file' => 'app/models/Member.php',
        'mustContain' => [
            'm.id_number LIKE :search_id_number',
            '(m.package = :package OR m.package_key = :package)',
        ],
    ],
    [
        'file' => 'app/controllers/AgentDashboardController.php',
        'mustContain' => [
            "'corporate_couple_count' => \$corporateCoupleCount",
            '$monthlyContribution = $this->memberModel->calculateMonthlyContribution($memberForCalc, [])',
        ],
    ],
    [
        'file' => 'app/controllers/AgentController.php',
        'mustContain' => [
            '$userUpdateData = [',
            '$agentUpdateData = [',
            '$this->userModel->update($agent[\'user_id\'], $userUpdateData)',
            '$this->agentModel->updateAgent($agentId, $agentUpdateData)',
            'notifyAgentManagementChange',
        ],
    ],
    [
        'file' => 'resources/views/admin/register-member.php',
        'mustContain' => [
            'membershipPlanData',
            'data-monthly-contribution',
            'corporate-total-preview',
        ],
        'mustNotContain' => [
            'name="sub_county"',
            'Sub County',
            'Sub-County',
            '+KES 150',
            '+KES 300',
        ],
    ],
    [
        'file' => 'resources/views/agent/register-member.php',
        'mustContain' => [
            'membershipPlanData',
            'data-monthly-contribution',
            'corporate-total-preview',
        ],
        'mustNotContain' => [
            '+KES 150',
            '+KES 300',
        ],
    ],
    [
        'file' => 'resources/views/admin/member-edit.php',
        'mustContain' => [
            'profile-edit-shell',
            'member status',
            'name="package_key"',
            'data-monthly-contribution',
        ],
        'mustNotContain' => [
            'name="sub_county"',
            'Sub County',
            'Sub-County',
        ],
    ],
    [
        'file' => 'resources/views/admin/agent-edit.php',
        'mustContain' => [
            'profile-edit-shell',
            'agent status',
        ],
    ],
    [
        'file' => 'resources/views/admin/members.php',
        'mustContain' => [
            'member-quick-panel',
            'openMemberManageModal',
            'memberStatusSelect',
            'memberSearchForm',
            'memberManageForm',
            'name="search"',
            'name="package"',
            'name="return_to"',
        ],
        'mustNotContain' => [
            '<i class="fas fa-edit"></i> Edit',
            '<i class="fas fa-money-bill-wave"></i> Payments',
            '<i class="fas fa-ban"></i> Suspend',
            '<i class="fas fa-check"></i> Activate',
            'filterMembers()',
        ],
    ],
    [
        'file' => 'resources/views/admin/agents.php',
        'mustContain' => [
            'agent-quick-panel',
            'openAgentEditModal',
            'agentStatusSelect',
        ],
    ],
    [
        'file' => 'resources/views/layouts/admin-header.php',
        'mustContain' => [
            '/admin/plan-upgrades',
            'Plan Upgrades',
        ],
    ],
];

$failed = false;

require_once $root . '/app/services/MembershipPricingService.php';
$corporatePricing = MembershipPricingService::calculateMonthlyContribution([
    'tier' => 'executive',
    'principal_age' => 40,
    'corporate_couple_count' => 2,
]);
if (($corporatePricing['base_price'] ?? 0) !== 300 || ($corporatePricing['corporate_addon'] ?? 0) !== 600 || ($corporatePricing['total_price'] ?? 0) !== 900) {
    fwrite(STDERR, "Corporate pricing should charge the selected plan amount for each additional corporate couple\n");
    $failed = true;
}

foreach ($checks as $check) {
    $path = $root . '/' . $check['file'];
    if (!file_exists($path)) {
        fwrite(STDERR, $check['file'] . " does not exist\n");
        $failed = true;
        continue;
    }

    $contents = file_get_contents($path);

    foreach ($check['mustContain'] ?? [] as $needle) {
        if (strpos($contents, $needle) === false) {
            fwrite(STDERR, $check['file'] . " is missing expected marker: {$needle}\n");
            $failed = true;
        }
    }

    foreach ($check['mustNotContain'] ?? [] as $needle) {
        if (strpos($contents, $needle) !== false) {
            fwrite(STDERR, $check['file'] . " still contains forbidden marker: {$needle}\n");
            $failed = true;
        }
    }
}

$memberModelContents = file_get_contents($root . '/app/models/Member.php');
if (substr_count($memberModelContents, 'm.id_number LIKE :search_id_number') < 2) {
    fwrite(STDERR, "Member search should include national ID in both member listing queries\n");
    $failed = true;
}

exit($failed ? 1 : 0);
