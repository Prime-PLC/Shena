<?php

$root = dirname(__DIR__);
$failed = false;

$assertContains = static function (string $contents, string $needle, string $message) use (&$failed): void {
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, $message . "\nMissing: " . $needle . "\n");
        $failed = true;
    }
};

$assertNotContains = static function (string $contents, string $needle, string $message) use (&$failed): void {
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, $message . "\nUnexpected: " . $needle . "\n");
        $failed = true;
    }
};

$memberModel = file_get_contents($root . '/app/models/Member.php');
$paymentModel = file_get_contents($root . '/app/models/Payment.php');
$adminController = file_get_contents($root . '/app/controllers/AdminController.php');
$paymentsView = file_get_contents($root . '/resources/views/admin/payments.php');
$database = file_get_contents($root . '/app/core/Database.php');

$assertContains(
    $memberModel,
    "\$this->hasColumn('package_key')",
    'Member package filtering must guard package_key so older cPanel schemas do not 500.'
);
$assertContains(
    $memberModel,
    "m.package = :package",
    'Member package filtering should keep legacy package support.'
);

foreach (['search', 'date_from', 'date_to', 'payment_method', 'payment_type', 'reconciliation_status'] as $filterKey) {
    $assertContains(
        $adminController,
        "'{$filterKey}' => \$this->queryString('{$filterKey}')",
        "Admin payments controller should collect {$filterKey} filter."
    );
}
$assertContains($adminController, "private function queryString(string \$key", 'Admin payments should normalize query params before trimming.');
$assertContains($adminController, 'is_array($value)', 'Admin payments query helper should reject array-valued filters.');
$assertContains($adminController, "'current_page' => \$page", 'Admin payments should pass pagination metadata.');
$assertContains($adminController, 'getAllPaymentsWithDetails($filters, $perPage, $offset)', 'Admin payments should query with limit and offset.');
$assertContains($adminController, 'getAllPaymentsWithDetailsCount($filters)', 'Admin payments should query total filtered count.');

$assertContains($paymentModel, 'buildPaymentFilterClause', 'Payment filters should be centralized in the model.');
$assertContains($paymentModel, 'LIMIT :limit OFFSET :offset', 'Payment list query should be paginated.');
$assertContains($paymentModel, 'getAllPaymentsWithDetailsCount', 'Payment model should expose filtered count.');
$assertContains($paymentModel, 'p.payment_method = :payment_method', 'Payment method filter should be supported.');
$assertContains($paymentModel, 'p.reconciliation_status = :reconciliation_status', 'Reconciliation status filter should be supported.');
$assertContains($paymentModel, 'p.created_at >= :date_from', 'Date-from filter should be supported.');
$assertContains($paymentModel, 'p.created_at <= :date_to', 'Date-to filter should be supported.');

foreach ([
    'name="search"',
    'name="status"',
    'name="date_from"',
    'name="date_to"',
    'name="payment_method"',
    'name="payment_type"',
    'name="reconciliation_status"',
] as $needle) {
    $assertContains($paymentsView, $needle, 'Payments view should expose real GET filter fields.');
}
$assertContains($paymentsView, 'paymentFiltersForm', 'Payments view should wrap filters in a real form.');
$assertContains($paymentsView, 'paymentPagination', 'Payments view should render pagination controls.');
$assertContains($paymentsView, 'paymentSearchInput', 'Payments search field should remain connected to the filter form.');
$assertNotContains($paymentsView, 'paymentSearchTimer', 'Payments search should not submit or refresh the page on every typed character.');
$assertNotContains($paymentsView, "paymentSearchInput?.addEventListener('input'", 'Payments search should wait for explicit submit/Enter instead of typing refresh.');
$assertNotContains($paymentsView, '<button class="filter-btn">' . "\n" . '                    <i class="fas fa-calendar"></i>', 'Payments date range button should not remain stale.');

$assertContains($database, 'PDO::PARAM_INT', 'Database wrapper should bind integer params as integers for LIMIT/OFFSET pagination.');

exit($failed ? 1 : 0);
