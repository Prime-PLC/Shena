<?php

$root = dirname(__DIR__);
require_once $root . '/app/core/Database.php';
require_once $root . '/app/services/PaymentStatusService.php';

$failed = false;

$assertSame = static function ($expected, $actual, string $message) use (&$failed): void {
    if ($expected !== $actual) {
        fwrite(STDERR, $message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true) . "\n");
        $failed = true;
    }
};

$assertFloat = static function (float $expected, $actual, string $message) use (&$failed): void {
    if (abs($expected - (float)$actual) > 0.001) {
        fwrite(STDERR, $message . "\nExpected: {$expected}\nActual: {$actual}\n");
        $failed = true;
    }
};

$service = new PaymentStatusService();

$member = [
    'id' => 1,
    'created_at' => '2026-05-01 00:00:00',
    'monthly_contribution' => 100,
];

$snapshot = $service->buildMonthlyPaymentSnapshot($member, [
    ['status' => 'completed', 'payment_type' => 'monthly', 'amount' => 100, 'payment_date' => '2026-05-05 10:00:00'],
    ['status' => 'completed', 'payment_type' => 'monthly', 'amount' => 40, 'payment_date' => '2026-06-05 10:00:00'],
], new DateTime('2026-07-08'));

$assertSame('defaulted', $snapshot['payment_group'], 'Two missed/short months after the 7th should be defaulted.');
$assertSame(2, $snapshot['missed_months'], 'Missed months should include each due month below the contribution.');
$assertFloat(160.0, $snapshot['balance_due'], 'Payment Balance should be cumulative outstanding balance, not only current monthly contribution.');
$assertFloat(160.0, $snapshot['amount_due'], 'Campaign amount_due should mirror cumulative Payment Balance.');

$priorBalance = $service->buildMonthlyPaymentSnapshot($member, [
    ['status' => 'completed', 'payment_type' => 'monthly', 'amount' => 100, 'payment_date' => '2026-05-05 10:00:00'],
    ['status' => 'completed', 'payment_type' => 'monthly', 'amount' => 40, 'payment_date' => '2026-06-05 10:00:00'],
    ['status' => 'completed', 'payment_type' => 'monthly', 'amount' => 100, 'payment_date' => '2026-07-05 10:00:00'],
], new DateTime('2026-07-08'));

$assertSame('in_arrears', $priorBalance['payment_group'], 'Current-month payment should not hide an older outstanding balance.');
$assertFloat(60.0, $priorBalance['balance_due'], 'Prior shortfall should remain visible as Payment Balance.');

$untypedPayment = $service->buildMonthlyPaymentSnapshot($member, [
    ['status' => 'completed', 'payment_type' => '', 'amount' => 100, 'payment_date' => '2026-07-05 10:00:00'],
], new DateTime('2026-07-08'));

$assertSame('defaulted', $untypedPayment['payment_group'], 'Untyped completed contribution payments should count toward paid amount but not hide older missed months.');
$assertFloat(100.0, $untypedPayment['paid_amount'], 'Untyped completed contribution payments should count as current-month paid amount.');

$beforeDeadlineUnpaid = $service->buildMonthlyPaymentSnapshot([
    'id' => 2,
    'created_at' => '2026-07-01 00:00:00',
    'monthly_contribution' => 100,
], [], new DateTime('2026-07-02'));

$assertSame('unpaid_current', $beforeDeadlineUnpaid['payment_group'], 'No current-month payment should appear under Not Paid before the 7th so campaign audiences stay usable.');
$assertFloat(100.0, $beforeDeadlineUnpaid['balance_due'], 'Before-deadline unpaid members should still show the current payment balance.');

$advancePayments = [
    ['status' => 'completed', 'payment_type' => 'monthly', 'amount' => 600, 'payment_date' => '2026-05-05 10:00:00'],
];
$advancePayment = $service->buildMonthlyPaymentSnapshot($member, $advancePayments, new DateTime('2026-07-08'));
$advanceCoverage = $service->buildContributionCoverageSnapshot($member, $advancePayments, new DateTime('2026-07-08'));

$assertSame('defaulted', $advancePayment['payment_group'], 'Advance coverage must not change the existing Payment Breakdown grouping.');
$assertFloat(0.0, $advanceCoverage['coverage_balance_due'], 'Advance-paid members must not enter reminder audiences.');
$assertFloat(300.0, $advanceCoverage['contribution_credit'], 'The unused amount should remain available for later months.');
$assertSame('2026-10-31', $advanceCoverage['covered_through'], 'Advance credit should expose the last fully covered month.');

$futureCoveredMonth = $service->buildContributionCoverageSnapshot($member, $advancePayments, new DateTime('2026-10-08'));

$assertFloat(0.0, $futureCoveredMonth['coverage_balance_due'], 'Carried contribution credit should continue covering later reminder months.');
$assertFloat(0.0, $futureCoveredMonth['contribution_credit'], 'Credit should be consumed as each covered month becomes due.');

exit($failed ? 1 : 0);
