<?php

/**
 * Applies a normal monthly payment to Basic first and then to each Platinum
 * coverage group. Platinum-only payments remain supported as an exception.
 */
class PlatinumBillingService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function monthlyAmount(array $member): float
    {
        $basic = (float) ($member['monthly_contribution'] ?? 0);
        $platinum = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(monthly_contribution), 0) FROM platinum_coverages
             WHERE member_id = :member_id AND status IN ('pending_payment', 'pending_approval', 'active')",
            ['member_id' => (int) $member['id']]
        );
        return $basic + (float) $platinum;
    }

    public function applyMonthlyPayment(int $paymentId, int $memberId, string $paidAt = ''): array
    {
        $payment = $this->db->fetch('SELECT * FROM payments WHERE id = :id AND member_id = :member_id', ['id' => $paymentId, 'member_id' => $memberId]);
        if (!$payment || ($payment['payment_type'] ?? '') !== 'monthly' || ($payment['status'] ?? '') !== 'completed') {
            return ['allocated' => 0.0, 'deficit' => 0.0];
        }

        $member = $this->db->fetch('SELECT id, monthly_contribution FROM members WHERE id = :id', ['id' => $memberId]);
        $coverages = $this->db->fetchAll(
            "SELECT * FROM platinum_coverages WHERE member_id = :member_id
             AND status IN ('pending_payment', 'pending_approval', 'active') ORDER BY id ASC",
            ['member_id' => $memberId]
        );
        $platinumDue = array_sum(array_map(static fn($coverage) => (float) $coverage['monthly_contribution'], $coverages));
        $basicDue = max(0, (float) ($member['monthly_contribution'] ?? 0));
        $remaining = max(0, (float) $payment['amount'] - $basicDue);
        $allocated = 0.0;
        $date = $paidAt ?: date('Y-m-d H:i:s');

        foreach ($coverages as $coverage) {
            $due = (float) $coverage['monthly_contribution'];
            $amount = min($due, $remaining);
            if ($amount <= 0) continue;
            $existing = $this->db->fetch(
                'SELECT id FROM platinum_payment_allocations WHERE payment_id = :payment_id AND platinum_coverage_id = :coverage_id',
                ['payment_id' => $paymentId, 'coverage_id' => (int) $coverage['id']]
            );
            if (!$existing) {
                $this->db->insert('platinum_payment_allocations', [
                    'payment_id' => $paymentId,
                    'platinum_coverage_id' => (int) $coverage['id'],
                    'allocated_amount' => $amount,
                    'allocation_month' => date('Y-m-01', strtotime($date)),
                ]);
            }
            $update = ['last_payment_at' => $date, 'payment_reference' => $payment['mpesa_receipt_number'] ?? null];
            if ($coverage['status'] === 'pending_payment' && $amount >= $due) {
                $update['status'] = 'pending_approval';
                $update['requested_at'] = $date;
            }
            $this->db->update('platinum_coverages', $update, 'id = :id', ['id' => $coverage['id']]);
            $allocated += $amount;
            $remaining -= $amount;
        }

        return ['allocated' => $allocated, 'deficit' => max(0, $platinumDue - $allocated)];
    }
}
