<?php

/**
 * Platinum replaces the Basic contribution for each selected coverage group.
 * A normal monthly payment therefore covers Basic-only groups plus the
 * Platinum rate for every group that has selected Platinum.
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
        $coverages = $this->coveragesForMember((int) $member['id']);
        return $this->basicDueAfterPlatinumReplacement($member, $coverages)
            + $this->platinumDue($coverages);
    }

    public function applyMonthlyPayment(int $paymentId, int $memberId, string $paidAt = ''): array
    {
        $payment = $this->db->fetch('SELECT * FROM payments WHERE id = :id AND member_id = :member_id', ['id' => $paymentId, 'member_id' => $memberId]);
        if (!$payment || ($payment['payment_type'] ?? '') !== 'monthly' || ($payment['status'] ?? '') !== 'completed') {
            return ['allocated' => 0.0, 'deficit' => 0.0];
        }

        $member = $this->db->fetch('SELECT id, monthly_contribution FROM members WHERE id = :id', ['id' => $memberId]);
        $coverages = $this->coveragesForMember($memberId);
        $platinumDue = $this->platinumDue($coverages);
        $basicDue = $this->basicDueAfterPlatinumReplacement($member ?: [], $coverages);
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

    private function coveragesForMember(int $memberId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM platinum_coverages WHERE member_id = :member_id
             AND status IN ('pending_payment', 'pending_approval', 'active') ORDER BY id ASC",
            ['member_id' => $memberId]
        );
    }

    private function platinumDue(array $coverages): float
    {
        return array_sum(array_map(static fn($coverage) => (float) $coverage['monthly_contribution'], $coverages));
    }

    /**
     * members.monthly_contribution remains the stored Basic account total. For
     * every Platinum group, remove that group's Basic amount before adding its
     * Platinum price. This keeps the Basic record/history intact while charging
     * Platinum as a replacement tier, never as a second contribution.
     */
    private function basicDueAfterPlatinumReplacement(array $member, array $coverages): float
    {
        $memberId = (int) ($member['id'] ?? 0);
        $accountBasicTotal = max(0, (float) ($member['monthly_contribution'] ?? 0));
        if ($memberId < 1 || !$coverages) {
            return $accountBasicTotal;
        }

        $corporateGroups = $this->db->fetchAll(
            "SELECT id, monthly_contribution FROM member_corporate_members
             WHERE member_id = :member_id AND status = 'active'",
            ['member_id' => $memberId]
        );
        $corporateAmounts = [];
        $corporateTotal = 0.0;
        foreach ($corporateGroups as $corporate) {
            $amount = max(0, (float) ($corporate['monthly_contribution'] ?? 0));
            $corporateAmounts[(int) $corporate['id']] = $amount;
            $corporateTotal += $amount;
        }
        $principalBasic = max(0, $accountBasicTotal - $corporateTotal);
        $replacedBasic = 0.0;
        $principalReplaced = false;
        foreach ($coverages as $coverage) {
            if (($coverage['covered_person_type'] ?? '') === 'principal') {
                if (!$principalReplaced) {
                    $replacedBasic += $principalBasic;
                    $principalReplaced = true;
                }
                continue;
            }
            if (($coverage['covered_person_type'] ?? '') === 'corporate_member') {
                $replacedBasic += $corporateAmounts[(int) ($coverage['covered_person_id'] ?? 0)] ?? 0.0;
            }
        }
        return max(0, $accountBasicTotal - $replacedBasic);
    }
}
