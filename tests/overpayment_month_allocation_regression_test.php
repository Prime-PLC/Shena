<?php

// Regression: a payment covers the month it was made for (even when it exceeds the monthly
// amount); only the excess goes to earlier unpaid months. Status, history and monthly
// summaries must all use the original payment date and agree with that allocation.

$root = dirname(__DIR__);
$failures = [];
$expect = static function (bool $ok, string $message) use (&$failures): void {
    if (!$ok) {
        $failures[] = $message;
    }
};

if (!class_exists('BaseModel')) {
    class BaseModel {}
}
require_once $root . '/app/services/PaymentStatusService.php';
$service = (new ReflectionClass('PaymentStatusService'))->newInstanceWithoutConstructor();

$pay = static fn(string $date, float $amount): array => [
    'status' => 'completed', 'payment_type' => 'monthly', 'amount' => $amount,
    'payment_date' => $date . ' 10:00:00', 'created_at' => '2026-01-15 08:00:00', // created_at must not matter
];
$member = static fn(string $created): array => ['id' => 0, 'created_at' => $created, 'monthly_contribution' => 1000];
$asOf = new DateTime('2025-12-28');

// A. Exact payment marks the month paid.
$snap = $service->buildMonthlyPaymentSnapshot($member('2025-12-01'), [$pay('2025-12-03', 1000)], $asOf);
$expect($snap['paid_amount'] == 1000 && $snap['payment_group'] === 'paid_current', 'Exact monthly payment must show the month as paid.');

// B. Overpayment: its own month is paid; the excess is advance credit, not a reason to show unpaid.
$payments = [$pay('2025-12-03', 2500)];
$before = $payments;
$snap = $service->buildMonthlyPaymentSnapshot($member('2025-12-01'), $payments, $asOf);
$periods = $service->buildContributionAllocationSnapshot($member('2025-12-01'), $payments, $asOf)['periods'];
$expect($snap['paid_amount'] == 1000 && $snap['payment_group'] === 'paid_current', 'An overpayment must not make the month it covers look unpaid.');
$expect(($periods['2026-01-01']['paid'] ?? 0) == 1000 && ($periods['2026-02-01']['paid'] ?? 0) == 500, 'Excess beyond the month must be carried forward as advance credit.');
$expect($payments === $before, 'Allocation must not change the original payment record or its date.');

// C. Overpayment with arrears: current month first, only the excess goes back to older unpaid months.
$snap = $service->buildMonthlyPaymentSnapshot($member('2025-10-01'), [$pay('2025-10-10', 1000), $pay('2025-12-03', 1500)], $asOf);
$periods = $service->buildContributionAllocationSnapshot($member('2025-10-01'), [$pay('2025-10-10', 1000), $pay('2025-12-03', 1500)], $asOf)['periods'];
$expect(($periods['2025-12-01']['paid'] ?? 0) == 1000, 'The payment month must be settled first.');
$expect(($periods['2025-11-01']['paid'] ?? 0) == 500, 'Only the excess (500) may be applied to the earlier unpaid month.');
$expect($snap['missed_months'] === 1 && $snap['arrears_amount'] == 500, 'The remaining arrears must be reported consistently with the allocation.');

// D. A payment must not be swallowed by older arrears when it has no excess.
$periods = $service->buildContributionAllocationSnapshot($member('2025-10-01'), [$pay('2025-12-03', 1000)], $asOf)['periods'];
$expect(($periods['2025-12-01']['paid'] ?? 0) == 1000 && ($periods['2025-11-01']['paid'] ?? 0) == 0 && ($periods['2025-10-01']['paid'] ?? 0) == 0,
    'A payment with no excess must cover its own month, not older arrears.');

// E. Monthly summaries/history use the payment date (not the row creation time) and include the year.
$paymentModel = file_get_contents($root . '/app/models/Payment.php');
$report = file_get_contents($root . '/app/services/ReportService.php');
$expect(strpos($paymentModel, 'AND YEAR(COALESCE(payment_date, created_at)) = :year') !== false
    && strpos($paymentModel, 'AND MONTH(COALESCE(payment_date, created_at)) = :month') !== false, 'Member monthly payment summary must group by payment_date.');
$expect(strpos($paymentModel, 'AND MONTH(COALESCE(payment_date, created_at)) = MONTH(CURDATE())') !== false, 'Monthly revenue must group by payment_date.');
$expect(strpos($report, 'SELECT MONTH(COALESCE(payment_date, created_at)) AS month_no') !== false, 'Revenue chart must group by payment_date.');

// The edited files must still be valid PHP.
foreach (['app/models/Payment.php', 'app/services/ReportService.php'] as $file) {
    $tokens = token_get_all(file_get_contents($root . '/' . $file), TOKEN_PARSE);
    $expect($tokens !== [], $file . ' must parse.');
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}
echo "Overpayment month allocation regression checks passed.\n";
