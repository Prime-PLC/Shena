<?php

$root = dirname(__DIR__);

$checks = [
    [
        'file' => 'app/helpers/MemberNumberHelper.php',
        'mustContain' => [
            "private const PREFIX = 'SH-'",
            'random_int(100000, 999999)',
            "while (self::exists(\$memberNumber))",
            "self::PREFIX . random_int(100000, 999999)",
        ],
        'mustNotContain' => [
            'SHENA-{$year}-{$token}',
            "SELECT member_number FROM members WHERE member_number LIKE :pattern ORDER BY member_number DESC LIMIT 1 FOR UPDATE",
        ],
    ],
    [
        'file' => 'app/controllers/AuthController.php',
        'mustContain' => [
            "// Do not create a placeholder registration payment here.",
            "\$paymentData = null;",
            "if (\$paymentData !== null) {",
        ],
        'mustNotContain' => [
            "Welcome to SHENA! Mbr:",
            "Pay KES 200 reg fee via Paybill",
        ],
    ],
    [
        'file' => 'app/models/Payment.php',
        'mustContain' => [
            'sendRegistrationWelcomeSms',
            'cancelStaleRegistrationPaymentAttempts',
            "status = 'cancelled'",
            "payment_type = 'registration'",
        ],
    ],
    [
        'file' => 'app/controllers/MemberController.php',
        'mustContain' => [
            "\$stkStatus = null;",
            "'status' => \$stkStatus",
        ],
        'mustNotContain' => [
            'Send personalised welcome SMS only once after confirmed registration payment',
            "Welcome SMS error",
        ],
    ],
    [
        'file' => 'app/services/PaymentReconciliationService.php',
        'mustContain' => [
            'activateMemberAfterRegistrationPayment',
        ],
        'mustNotContain' => [
            "status' => 'active'",
            "coverage_ends' => date('Y-m-d', strtotime('+1 year'))",
        ],
    ],
    [
        'file' => 'resources/views/member/payments.php',
        'mustContain' => [
            'const PAYMENT_AMOUNTS',
            'registration',
            'reactivation',
            'monthly',
            'syncPaymentAmount',
            "paymentTypeSelect.addEventListener('change', syncPaymentAmount)",
            'id="retrySTKBtn"',
            'function showStkRetry',
            "retrySTKBtn?.addEventListener('click'",
        ],
    ],
    [
        'file' => 'resources/views/member/dashboard.php',
        'mustContain' => [
            'id="onb-pay-retry-btn"',
            'function showOnboardingRetry',
            "payRetryBtn.addEventListener('click'",
        ],
    ],
    [
        'file' => 'resources/views/auth/registration-complete.php',
        'mustContain' => [
            'id="retryRegistrationSTKBtn"',
            'function showRegistrationRetry',
            "retryRegistrationSTKBtn?.addEventListener('click'",
        ],
    ],
    [
        'file' => 'app/controllers/PaymentController.php',
        'mustContain' => [
            'resolveMemberPaymentAmount',
            "case 'registration':",
            "case 'reactivation':",
            "case 'monthly':",
        ],
    ],
];

$failed = false;

foreach ($checks as $check) {
    $path = $root . '/' . $check['file'];
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

exit($failed ? 1 : 0);
