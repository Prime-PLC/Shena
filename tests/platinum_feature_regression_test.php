<?php

$root = dirname(__DIR__);
require_once $root . '/app/services/PlatinumPricingService.php';

$membership_packages = require $root . '/config/packages.php';
$platinum_config = require $root . '/config/platinum.php';
$pricing = new PlatinumPricingService();
$quotes = [
    ['individual_71_80', '1950-01-01', 550.0],
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

$checks = [
    'app/controllers/AuthController.php' => [
        "'packages' => \$membership_packages",
        "\$platinumOptIn = (\$_POST['platinum_opt_in'] ?? '') === '1'",
    ],
    'resources/views/public/register-public.php' => [
        'name="package_id"',
        'name="platinum_opt_in"',
        'id="productSummary"',
        'function platinumPrice(age)',
    ],
    'resources/views/agent/register-member.php' => [
        'agentPlatinumPanel',
        "agentPlatinumPanel');",
        "agentPlatinumOptIn')?.addEventListener('change', updateAgentPlatinumPrice)",
        'Platinum replaces the principal Basic rate',
    ],
    'app/controllers/AdminController.php' => [
        "\$tier = in_array(\$_GET['tier'] ?? 'all', ['all', 'basic', 'platinum'], true)",
        "'Product Tier'",
        "(new PlatinumPricingService())->quote(\$packageKey, \$dateOfBirth)",
        "'package_key' => \$quote['package_key']",
    ],
    'database/migrations/023_legacy_medical_placeholder_audit.sql' => [
        'legacy_medical_corporate_archive',
        'This migration deliberately does not move or delete member data.',
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
        'basicDueAfterPlatinumReplacement',
        'Platinum replaces the Basic contribution for each selected coverage group.',
        'return $this->basicDueAfterPlatinumReplacement($member, $coverages)',
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

exit($failed ? 1 : 0);
