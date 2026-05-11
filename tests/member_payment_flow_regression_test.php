<?php

$root = dirname(__DIR__);

$checks = [
    [
        'file' => 'resources/views/member/dashboard.php',
        'mustContain' => [
            'let registrationCheckoutRequestId',
            "registrationCheckoutRequestId = data.checkout_request_id",
            "checkout_request_id: registrationCheckoutRequestId",
        ],
    ],
    [
        'file' => 'app/controllers/MemberController.php',
        'mustContain' => [
            'reconcileRegistrationCheckout',
            "checkout_request_id",
            'queryTransactionStatus($checkoutRequestId)',
            'checkout_request_id = :checkout_id_alt',
            'confirmPayment($payment[\'id\']',
            '$_POST[\'mpesa_code\']',
            'wantsJsonResponse',
            "\$requestedWith === 'xmlhttprequest'",
            "Payment verified successfully",
        ],
    ],
    [
        'file' => 'resources/views/member/payments.php',
        'mustContain' => [
            'name="phone_number"',
            'name="transaction_code"',
            "formData.append('csrf_token'",
            "'X-Requested-With': 'XMLHttpRequest'",
        ],
    ],
    [
        'file' => 'public/mpesa-stk-callback.php',
        'mustContain' => [
            'checkout_request_id = :checkout_id',
            'checkout_id_alt',
            'merchant_request_id = :merchant_request_id',
            'result_code = :result_code',
            '196.201.212.0/24',
            '196.201.213.0/24',
            '196.201.214.0/24',
            '196.201.215.0/24',
        ],
    ],
    [
        'file' => 'public/mpesa-c2b-callback.php',
        'mustContain' => [
            "app/helpers/functions.php",
            '196.201.212.0/24',
            '196.201.213.0/24',
            '196.201.214.0/24',
            '196.201.215.0/24',
        ],
    ],
    [
        'file' => 'app/services/PaymentService.php',
        'mustContain' => [
            "'checkout_request_id' => \$checkoutRequestId",
            "'transaction_reference' => \$checkoutRequestId",
            'checkout_request_id = :checkout_id_alt',
            'merchant_request_id',
            'result_code',
        ],
    ],
    [
        'file' => 'app/models/Payment.php',
        'mustContain' => [
            'mpesa_receipt_number = :receipt',
            'transaction_id = :receipt_txn',
            "'receipt_txn' => \$receiptNumber",
        ],
    ],
    [
        'file' => 'app/services/PaymentReconciliationService.php',
        'mustContain' => [
            'return formatKenyanPhone($phone);',
            'activateMemberAfterRegistrationPayment',
            'mpesa_c2b_callbacks',
            'reconciliation_status',
        ],
    ],
    [
        'file' => 'app/models/Member.php',
        'mustContain' => [
            'formatKenyanPhone($phone)',
            'REPLACE(u.phone, \'+\', \'\')',
        ],
    ],
];

$failed = false;

foreach ($checks as $check) {
    $path = $root . '/' . $check['file'];
    $contents = file_get_contents($path);

    foreach ($check['mustContain'] as $needle) {
        if (strpos($contents, $needle) === false) {
            fwrite(STDERR, $check['file'] . " is missing expected marker: {$needle}\n");
            $failed = true;
        }
    }
}

exit($failed ? 1 : 0);
