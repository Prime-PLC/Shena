<?php

$root = dirname(__DIR__);

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
        'Basic \' + total.toLocaleString() + \' + Platinum \'',
    ],
    'app/controllers/AdminController.php' => [
        "\$tier = in_array(\$_GET['tier'] ?? 'all', ['all', 'basic', 'platinum'], true)",
        "'Product Tier'",
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
];

$failed = false;
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
