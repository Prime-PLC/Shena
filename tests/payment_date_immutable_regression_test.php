<?php

// Regression: reconciling/confirming a payment must keep its original payment date.
// The date decides which month a payment covers, so an incidental overwrite can leave
// the paid month looking unpaid. Corrections must be explicit and audited.

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
require_once $root . '/app/models/Payment.php';
require_once $root . '/app/services/PaymentStatusService.php';

// 1. Confirmation keeps the original date.
$expect(Payment::resolveConfirmationPaymentDate(['payment_date' => '2025-11-28 14:05:00'], '2025-12-02 09:00:00') === '2025-11-28 14:05:00',
    'Confirming must keep an existing payment_date.');
$expect(Payment::resolveConfirmationPaymentDate(['payment_date' => null, 'transaction_date' => '2025-11-27 10:00:00'], '2025-12-02 09:00:00') === '2025-11-27 10:00:00',
    'A missing payment_date should fall back to the M-Pesa transaction_date, not the confirmation time.');
$expect(Payment::resolveConfirmationPaymentDate(['payment_date' => '0000-00-00 00:00:00'], '2025-12-02 09:00:00') === '2025-12-02 09:00:00',
    'An empty/zero payment_date falls back to now.');
$expect(Payment::resolveConfirmationPaymentDate([], '2025-12-02 09:00:00') === '2025-12-02 09:00:00',
    'A payment with no dates falls back to now.');

// 2. Corrections are explicit: reason required, no future dates, valid date.
$today = new DateTimeImmutable('2025-12-05');
$expect(!Payment::validateDateCorrection('2025-11-28', '', $today)['ok'], 'A correction without a reason must be rejected.');
$expect(!Payment::validateDateCorrection('2025-12-20', 'typo', $today)['ok'], 'A future correction date must be rejected.');
$expect(!Payment::validateDateCorrection('nonsense', 'typo', $today)['ok'], 'An invalid correction date must be rejected.');
$ok = Payment::validateDateCorrection('2025-11-28', 'Receipt shows 28 Nov', $today);
$expect($ok['ok'] && $ok['date'] === '2025-11-28 00:00:00', 'A valid, reasoned correction should be accepted.');

// 3. Why it matters: the allocation month comes from payment_date.
$service = (new ReflectionClass('PaymentStatusService'))->newInstanceWithoutConstructor();
$member = ['id' => 0, 'created_at' => '2025-11-01', 'monthly_contribution' => 1000];
$asOf = new DateTime('2025-12-05');
$payment = ['status' => 'completed', 'payment_type' => 'monthly', 'amount' => 1000, 'created_at' => '2025-12-02 09:00:00'];

$original = $service->buildContributionAllocationSnapshot($member, [$payment + ['payment_date' => '2025-11-28 14:05:00']], $asOf)['periods'];
$expect(($original['2025-11-01']['paid'] ?? 0) == 1000 && ($original['2025-12-01']['paid'] ?? 0) == 0,
    'A November payment with its original date must mark November paid.');
$overwritten = $service->buildContributionAllocationSnapshot($member, [$payment + ['payment_date' => '2025-12-02 09:00:00']], $asOf)['periods'];
$expect(($overwritten['2025-11-01']['paid'] ?? 0) == 0,
    'Sanity check: an overwritten (December) date moves the payment to December, which is the bug being prevented.');

// Excess still goes to the prior unpaid month, without touching the original date.
$excess = $service->buildContributionAllocationSnapshot($member, [['status' => 'completed', 'payment_type' => 'monthly', 'amount' => 2000, 'payment_date' => '2025-12-03 10:00:00', 'created_at' => '2025-12-03 10:00:00']], $asOf)['periods'];
$expect(($excess['2025-12-01']['paid'] ?? 0) == 1000 && ($excess['2025-11-01']['paid'] ?? 0) == 1000,
    'An overpayment must pay its own month first and only the excess goes to the earlier unpaid month.');

// 4. Contract checks across the write paths and the displays.
$recon = file_get_contents($root . '/app/services/PaymentReconciliationService.php');
$model = file_get_contents($root . '/app/models/Payment.php');
$service_src = file_get_contents($root . '/app/services/PaymentService.php');
$memberView = file_get_contents($root . '/resources/views/member/payments.php');
$adminView = file_get_contents($root . '/resources/views/admin/payments.php');

if (preg_match('/UPDATE payments SET\s+member_id\s*=.*?WHERE id = :payment_id/s', $recon, $m)) {
    $expect(stripos($m[0], 'payment_date') === false, 'Manual reconciliation UPDATE must not touch payment_date.');
    $expect(stripos($m[0], 'reconciled_at') !== false, 'Reconciliation time must be stored separately in reconciled_at.');
} else {
    $failures[] = 'Could not find the manual reconciliation UPDATE.';
}
$expect(preg_match('/function confirmPayment.*?resolveConfirmationPaymentDate/s', $model) === 1, 'confirmPayment must use resolveConfirmationPaymentDate.');
$expect(preg_match("/function confirmPayment.*?'payment_date' => date\\('Y-m-d H:i:s'\\)/s", $model) === 0, 'confirmPayment must not stamp payment_date with now.');
$expect(strpos($model, "'date_corrected'") !== false, 'Date corrections must be written to payment_reconciliation_log.');
$expect(strpos($service_src, '$paymentDate = null') !== false, 'Manual payment entry must accept the real payment date.');
$expect(strpos($memberView, "\$payment['payment_date'] ?? \$payment['created_at']") !== false, 'Member payment history must show payment_date first.');
$expect(strpos($adminView, "\$p['payment_date'] ?? \$p['created_at']") !== false, 'Admin payment list must show payment_date first.');

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}
echo "Payment date immutability regression checks passed.\n";
