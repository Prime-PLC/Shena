<?php

$root = dirname(__DIR__);
$failed = false;

$checks = [
    [
        'file' => 'resources/views/admin/members.php',
        'mustContain' => [
            'pendingApprovalValidationForm',
            'action="/admin/payments/verify"',
            'name="mpesa_receipt_number"',
            'name="member_id"',
            'name="method" value="paybill"',
            'name="payment_type" value="registration"',
            'name="return_to" value="/admin/members"',
        ],
        'mustNotContain' => [
            "/admin/members/approve/",
        ],
    ],
    [
        'file' => 'app/controllers/PaymentController.php',
        'mustContain' => [
            'wantsJsonResponse',
            '$returnTo = $input[\'return_to\'] ?? \'/admin/payments\'',
            '$_SESSION[\'success\'] = $result[\'message\']',
            '$_SESSION[\'error\'] = $result[\'message\'] ?? \'Verification failed\'',
            'header(\'Location: \' . $returnTo)',
        ],
    ],
    [
        'file' => 'app/core/Router.php',
        'mustContain' => [
            "POST', '/admin/payments/verify'",
        ],
    ],
];

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

exit($failed ? 1 : 0);
