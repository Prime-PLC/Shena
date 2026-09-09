<?php

$root = dirname(__DIR__);
require_once $root . '/app/services/PlatinumPricingService.php';

$membership_packages = require $root . '/config/packages.php';
$platinum_config = require $root . '/config/platinum.php';
$pricing = new PlatinumPricingService();
$quotes = [
    ['individual_71_80', '1950-01-01', 550.0],
    ['individual_71_80', (new DateTimeImmutable('today'))->modify('-70 years')->format('Y-m-d'), 550.0],
    ['couple_children_parents_inlaws_71_80', (new DateTimeImmutable('today'))->modify('-70 years')->format('Y-m-d'), 600.0],
    ['couple_children_below_70', '1990-01-01', 400.0],
    ['executive_above_70', '1950-01-01', 700.0],
];
$failed = false;
foreach ($quotes as [$packageKey, $dateOfBirth, $expectedAmount]) {
    $quote = $pricing->quote($packageKey, $dateOfBirth);
    if (!$quote || (float) $quote['amount'] !== $expectedAmount) {
        fwrite(STDERR, "Unexpected Platinum quote for {$packageKey}.\n");
        $failed = true;
    }
}
$invalidPackageAge = $pricing->quote('individual_71_80', '1990-01-01');
if (!$invalidPackageAge || (float)$invalidPackageAge['amount'] !== 550.0) {
    fwrite(STDERR, "A Platinum conversion must preserve the exact Basic package rate regardless of owner age band.\n");
    $failed = true;
}

$checks = [
    'app/controllers/AuthController.php' => [
        "'packages' => \$membership_packages",
        "\$platinumOptIn = (\$_POST['platinum_opt_in'] ?? '') === '1'",
    ],
    'resources/views/public/register-public.php' => [
        'name="package_id"',
        'name="platinum_opt_in"',
        'id="productSummary"',
        'function platinumPrice(packageKey)',
    ],
    'resources/views/agent/register-member.php' => [
        'agentPlatinumPanel',
        "agentPlatinumPanel');",
        "agentPlatinumOptIn')?.addEventListener('change', updateAgentPlatinumPrice)",
        'agentPlatinumPriceForPackage(packageKey)',
    ],
    'app/controllers/AdminController.php' => [
        "\$tier = in_array(\$_GET['tier'] ?? 'all', ['all', 'basic', 'platinum'], true)",
        "'Product Tier'",
        "(new PlatinumPricingService())->quote(\$packageKey, \$dateOfBirth)",
        "'package_key' => \$quote['package_key']",
        'No SMS was sent',
        'platinumMigrationOptions',
        'revertMemberPlatinumToBasic',
    ],
    'app/controllers/MemberController.php' => [
        'MembershipPricingService::calculateAccountMonthlyContribution',
        "(new PlatinumPricingService())->quote(\$packageId, \$dobForAge)",
        'Dependants inherit their owner',
    ],
    'app/services/PaymentStatusService.php' => [
        'liveMonthlyContribution',
        'PlatinumBillingService',
    ],
    'database/migrations/023_legacy_medical_placeholder_audit.sql' => [
        'legacy_medical_corporate_archive',
        'This migration deliberately does not move or delete member data.',
    ],
    'database/migrations/024_platinum_admin_approval_only.sql' => [
        'pending_approval',
        'pending_payment',
    ],
    'database/sql/legacy_medical_to_platinum.sql' => [
        'ONE-TIME LEGACY CONVERSION',
        'START TRANSACTION',
        'COMMIT',
        'Converted from reviewed legacy medical corporate placeholder',
    ],
    'database/sql/legacy_medical_to_platinum_preview.sql' => [
        'READ-ONLY PREVIEW',
        'READY: converts to principal Platinum coverage',
    ],
    'database/sql/repair_legacy_medical_member_contributions.sql' => [
        'ONE-TIME REPAIR FOR ACCOUNTS ALREADY CONVERTED',
        'Converted from reviewed legacy medical corporate placeholder',
        'current_monthly_payable',
    ],
    'resources/views/admin/payments.php' => [
        "'payment_type' => (\$paymentFilters['payment_type'] ?? '') !== 'all'",
    ],
    'app/services/ReportService.php' => [
        "'payment_type' => \$filters['payment_type'] ?? 'all'",
    ],
    'app/services/PlatinumEligibilityService.php' => [
        'SELECT * FROM inpatient_requests WHERE id = :id FOR UPDATE',
        "'partially_approved'",
        "'days_reserved'",
    ],
    'app/services/PlatinumBillingService.php' => [
        'public function accountSummary',
        'basicDueAfterPlatinumReplacement',
        'legacy "medical" line',
        'MembershipPricingService::resolveSelectedPackageAmount',
        'Platinum replaces the Basic contribution for each selected coverage group.',
        "return \$this->accountSummary(\$member)['total'];",
        "status = 'active' ORDER BY id ASC",
    ],
    'resources/views/admin/register-member.php' => [
        'let corporateTotal = 0;',
        'platinumPriceForPackage(packageKey, age)',
        "document.getElementById('platinumOptIn')?.addEventListener('change', updatePlatinumOptInPrice)",
    ],
    'resources/views/admin/member-details.php' => [
        'account_monthly_amount',
        'Monthly Contribution',
        'account_contribution_breakdown',
        'Account monthly contribution after migration',
        'Covered under',
        'Edit Corporate Members',
        'Return to Basic',
    ],
    'resources/views/member/payments.php' => [
        'account_monthly_amount',
        'Your Monthly Contribution',
    ],
    'resources/views/member/inpatient-requests.php' => [
        'maturity-lock',
        'The form will unlock automatically',
    ],
    'resources/views/member/platinum.php' => [
        'File hospital cover request',
        'requestable_principal',
        'If your cover is still maturing',
    ],
];

foreach ($checks as $file => $needles) {
    $contents = file_get_contents($root . '/' . $file);
    foreach ($needles as $needle) {
        if (strpos($contents, $needle) === false) {
            fwrite(STDERR, "$file is missing expected marker: $needle\n");
            $failed = true;
        }
    }
}

require_once $root . '/app/services/MembershipPricingService.php';
foreach ([69 => 'below_70', 70 => 'below_70', 71 => '71_80', 80 => '71_80', 81 => '81_90'] as $age => $band) {
    if (MembershipPricingService::resolveAgeBand($age) !== $band) {
        fwrite(STDERR, "Incorrect age band at {$age}.\n"); $failed = true;
    }
}
foreach ($membership_packages as $key => $package) {
    if (!empty($package['legacy_alias'])) continue;
    if (str_ends_with($key, '_below_70') && (int)$package['age_max'] !== 70) $failed = true;
    if (str_ends_with($key, '_71_80') && ((int)$package['age_min'] !== 71 || (int)$package['age_max'] !== 80)) $failed = true;
}
foreach (['resources/views/public/membership.php', 'resources/views/public/terms-and-conditions.php'] as $file) {
    if (preg_match('/70\s*[-–—]\s*80|below (?:age )?70/i', file_get_contents($root . '/' . $file))) {
        fwrite(STDERR, "Incorrect age-70 label in {$file}.\n"); $failed = true;
    }
}

exit($failed ? 1 : 0);
