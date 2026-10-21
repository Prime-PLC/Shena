<?php
require_once __DIR__ . '/../app/services/PaymentNaDiagnostic.php';
function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
$base = ['status' => 'completed', 'payment_type' => 'monthly', 'payment_date' => '2026-10-10 12:00:00'];
$none = PaymentNaDiagnostic::inspect([], '2026-10');
check($none['linked'] === 0 && strpos($none['reason'], 'No payments linked') === 0, 'No linked history');
$rows = [$base, array_merge($base, ['payment_type' => 'platinum']),
    array_merge($base, ['status' => 'pending']),
    array_merge($base, ['payment_date' => '', 'created_at' => '2026-10-01']),
    array_merge($base, ['payment_date' => null, 'created_at' => '2026-09-01']),
    array_merge($base, ['payment_type' => 'registration'])];
$result = PaymentNaDiagnostic::inspect($rows, '2026-10');
check($result['linked'] === 6 && $result['qualifying'] === 2, 'Accepted contribution history');
check($result['excluded_type'] === 2 && $result['completed_platinum'] === 1, 'Excluded types');
check($result['not_completed'] === 1 && $result['missing_date'] === 1, 'Status and blank date');
check($result['current_month_qualifying'] === 1, 'Older history is not current-month receipt');
check(PaymentNaDiagnostic::csvCell(' =HYPERLINK("bad")')[0] === "'", 'CSV formula protection');
check(PaymentNaDiagnostic::csvCell('SH-123456') === 'SH-123456', 'Member number retained');
echo "Payment N/A diagnostic tests passed.\n";
