<?php

/** Read-only explanation of the PaymentStatusService last-payment predicate. */
class PaymentNaDiagnostic
{
    public static function inspect(array $payments, string $month): array
    {
        $counts = ['linked' => count($payments), 'not_completed' => 0,
            'excluded_type' => 0, 'missing_date' => 0, 'qualifying' => 0,
            'completed_platinum' => 0, 'current_month_qualifying' => 0,
            'invalid_date' => 0];
        $types = [];
        $statuses = [];
        foreach ($payments as $payment) {
            $status = (string)($payment['status'] ?? '');
            $type = strtolower(trim((string)($payment['payment_type'] ?? '')));
            $types[$type ?: '(blank)'] = ($types[$type ?: '(blank)'] ?? 0) + 1;
            $statuses[$status ?: '(blank)'] = ($statuses[$status ?: '(blank)'] ?? 0) + 1;
            if ($status !== 'completed') {
                $counts['not_completed']++;
                continue;
            }
            if ($type === 'platinum') $counts['completed_platinum']++;
            if (!in_array($type, ['', 'monthly', 'contribution', 'monthly_contribution'], true)) {
                $counts['excluded_type']++;
                continue;
            }
            // Intentionally matches the report: an empty payment_date does not
            // fall back to created_at, whereas a NULL payment_date does.
            $date = $payment['payment_date'] ?? $payment['created_at'] ?? null;
            if (!$date) {
                $counts['missing_date']++;
                continue;
            }
            $counts['qualifying']++;
            $timestamp = strtotime($date);
            if ($timestamp === false) $counts['invalid_date']++;
            elseif (date('Y-m', $timestamp) === $month) $counts['current_month_qualifying']++;
        }
        $reasons = [];
        if (!$counts['linked']) $reasons[] = 'No payments linked to this member ID';
        if ($counts['not_completed']) $reasons[] = 'Payments not marked completed';
        if ($counts['excluded_type']) $reasons[] = 'Completed payments have excluded types';
        if ($counts['missing_date']) $reasons[] = 'Completed contributions have no usable date';
        if ($counts['qualifying']) $reasons = ['Qualifying contribution exists; last payment should not be N/A'];
        ksort($types);
        ksort($statuses);
        return $counts + ['reason' => implode('; ', $reasons),
            'types' => json_encode($types), 'statuses' => json_encode($statuses)];
    }

    public static function csvCell($value): string
    {
        $value = (string)$value;
        // Prevent spreadsheet formula execution, including after whitespace.
        return preg_match('/^[\s]*[=+@-]/u', $value) ? "'" . $value : $value;
    }
}
