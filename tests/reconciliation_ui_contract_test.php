<?php

$root = dirname(__DIR__);
$checks = [
    [
        'file' => 'app/controllers/PaymentController.php',
        'mustContain' => [
            "view('admin/payments-unmatched'",
            'getUnmatchedPayments',
            'findPotentialMatches',
            'getRecentReconciliationLogs',
            'reconciliation_filters',
            'member_number_query',
            'phone_tail',
        ],
        'mustNotContain' => [
            '$this->json($unmatchedPayments);',
        ],
    ],
    [
        'file' => 'resources/views/admin/payments-unmatched.php',
        'mustContain' => [
            'unmatched-shell',
            'Unmatched Payments',
            'Find matches',
            'Search member manually',
            'filter-form',
            'manual-search-modal',
            'audit-feed',
            'reconciliation_notes',
            'manualReconcile',
            '/admin/payments-reconciliation',
            '/admin/payments/',
            '/matches',
            '/admin/payments/reconcile',
            '/admin/payments/search-members',
            "event.key === 'Enter'",
        ],
    ],
    [
        'file' => 'app/controllers/AdminController.php',
        'mustContain' => [
            'new PaymentReconciliationService()',
            "'recon_stats' =>",
            "'unmatched_payments' =>",
            "'audit_logs' =>",
        ],
    ],
    [
        'file' => 'app/services/PaymentReconciliationService.php',
        'mustContain' => [
            'public function getUnmatchedPayments(array $filters = [])',
            'public function getRecentReconciliationLogs',
            'payment_reconciliation_log',
            'paybill_account LIKE :search',
        ],
    ],
    [
        'file' => 'resources/views/admin/payments.php',
        'mustContain' => [
            'Reconciliation',
            'reconciliation_notes',
            'reconciled_at',
            '$reconStats',
            'Review unmatched queue',
        ],
    ],
    [
        'file' => 'resources/views/admin/payments-reconciliation.php',
        'mustContain' => [
            'Unmatched Payment Queue',
            'Open matching queue',
            'Recent Reconciliation Audit',
        ],
        'mustNotContain' => [
            'VIEWING 3,436',
            'Revenue vs. Targets',
            'Paymf b/d 60drt',
            '$reconciled_payments',
        ],
    ],
];

$failed = false;

foreach ($checks as $check) {
    $path = $root . '/' . $check['file'];
    if (!file_exists($path)) {
        fwrite(STDERR, $check['file'] . " does not exist\n");
        $failed = true;
        continue;
    }

    $contents = file_get_contents($path);
    foreach ($check['mustContain'] as $needle) {
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
