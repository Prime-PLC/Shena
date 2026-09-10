<?php
require_once __DIR__ . "/PlatinumPricingService.php";

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
        return $this->accountSummary($member)['total'];
    }

    /** Return the account-wide payable amount without overwriting Basic history. */
    public function accountSummary(array $member): array
    {
        $coverages = $this->coveragesForMember((int) $member['id']);
        $basicDue = $this->basicDueAfterPlatinumReplacement($member, $coverages);
        $platinumDue = $this->platinumDue($coverages);
        $breakdown = $this->accountBreakdown($member, $coverages);
        return [
            'basic_due' => $basicDue,
            'platinum_due' => $platinumDue,
            'total' => $breakdown['total'],
            'breakdown' => $breakdown,
        ];
    }

    /** One display-safe source for the principal/corporate monthly arithmetic. */
    public function accountBreakdown(array $member, ?array $coverages = null): array
    {
        $memberId = (int)($member['id'] ?? 0);
        $coverages = $coverages ?? $this->coveragesForMember($memberId);
        $coverageByGroup = [];
        foreach ($coverages as $coverage) {
            $coverageByGroup[($coverage['covered_person_type'] ?? 'principal') . ':' . (int)($coverage['covered_person_id'] ?? 0)] = $coverage;
        }

        $packages = $GLOBALS['membership_packages'] ?? [];
        $principalBasic = MembershipPricingService::resolveSelectedPackageAmount(
            PlatinumPricingService::resolvePackageKey($member),
            $packages
        );
        $corporateGroups = $memberId > 0 ? $this->db->fetchAll(
            "SELECT id, label, package_key, monthly_contribution FROM member_corporate_members WHERE member_id = :member_id AND status = 'active'",
            ['member_id' => $memberId]
        ) : [];
        $corporateBasicTotal = array_sum(array_map(static fn($group) => (float)($group['monthly_contribution'] ?? 0), $corporateGroups));
        if ($principalBasic <= 0) {
            $principalBasic = max(0, (float)($member['monthly_contribution'] ?? 0) - $corporateBasicTotal);
        }

        $principalCoverage = $coverageByGroup['principal:0'] ?? null;
        $principalAmount = $principalCoverage ? (float)$principalCoverage['monthly_contribution'] : $principalBasic;
        $principalTier = $principalCoverage ? 'Platinum' : 'Basic';
        $corporateAmount = 0.0;
        $corporateTiers = [];
        foreach ($corporateGroups as $corporate) {
            $basicAmount = MembershipPricingService::resolveSelectedPackageAmount((string)($corporate['package_key'] ?? ''), $packages);
            if ($basicAmount <= 0) {
                $basicAmount = (float)($corporate['monthly_contribution'] ?? 0);
            }
            $coverage = $coverageByGroup['corporate_member:' . (int)$corporate['id']] ?? null;
            $corporateAmount += $coverage ? (float)$coverage['monthly_contribution'] : $basicAmount;
            $corporateTiers[] = $coverage ? 'Platinum' : 'Basic';
        }

        return [
            'principal_amount' => $principalAmount,
            'principal_tier' => $principalTier,
            'corporate_amount' => $corporateAmount,
            'corporate_count' => count($corporateGroups),
            'corporate_tier_label' => count(array_unique($corporateTiers)) === 1 ? ($corporateTiers[0] ?? 'Basic') : 'mixed',
            'total' => $principalAmount + $corporateAmount,
        ];
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
             AND status = 'active' ORDER BY id ASC",
            ['member_id' => $memberId]
        );
    }

    private function platinumDue(array $coverages): float
    {
        return array_sum(array_map(static fn($coverage) => (float) $coverage['monthly_contribution'], $coverages));
    }

    /**
     * Calculate the current Basic amount from the selected principal and active
     * corporate packages. This deliberately does not rely on the stored member
     * total: older accounts can still contain deleted legacy "medical" line
     * items in members.monthly_contribution. Every Platinum group replaces its
     * own Basic price, never adds a second contribution.
     */
    private function basicDueAfterPlatinumReplacement(array $member, array $coverages): float
    {
        $memberId = (int) ($member['id'] ?? 0);
        $storedAccountBasicTotal = max(0, (float) ($member['monthly_contribution'] ?? 0));
        if ($memberId < 1) {
            return $storedAccountBasicTotal;
        }

        $corporateGroups = $this->db->fetchAll(
            "SELECT id, package_key, monthly_contribution FROM member_corporate_members
             WHERE member_id = :member_id AND status = 'active'",
            ['member_id' => $memberId]
        );
        $membershipPackages = $GLOBALS['membership_packages'] ?? [];
        $principalAmount = MembershipPricingService::resolveSelectedPackageAmount(
            PlatinumPricingService::resolvePackageKey($member),
            $membershipPackages
        );
        $corporateAmounts = [];
        $corporateTotal = 0.0;
        foreach ($corporateGroups as $corporate) {
            $amount = MembershipPricingService::resolveSelectedPackageAmount(
                (string) ($corporate['package_key'] ?? ''),
                $membershipPackages
            );
            if ($amount <= 0) {
                $amount = max(0, (float) ($corporate['monthly_contribution'] ?? 0));
            }
            $corporateAmounts[(int) $corporate['id']] = $amount;
            $corporateTotal += $amount;
        }
        // Old records without a package key retain their stored amount as a
        // compatibility fallback. Current records always use the package rate.
        if ($principalAmount <= 0) {
            $principalAmount = max(0, $storedAccountBasicTotal - $corporateTotal);
        }
        $accountBasicTotal = $principalAmount + $corporateTotal;
        if (!$coverages) {
            return $accountBasicTotal;
        }
        $principalBasic = $principalAmount;
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
