<?php

/**
 * Central Platinum eligibility and calendar-year balance rules.
 */
class PlatinumEligibilityService
{
    private $db;
    private $wrapper;
    private $config;

    public function __construct()
    {
        $this->wrapper = Database::getInstance();
        $this->db = $this->wrapper->getConnection();
        global $platinum_config;
        $this->config = $platinum_config ?? [];
    }

    public function maturityMonths(int $age): int
    {
        return $age < 60
            ? (int) ($this->config['maturity_months']['under_60'] ?? 4)
            : (int) ($this->config['maturity_months']['60_and_above'] ?? 7);
    }

    public function maturityDate(string $effectiveFrom, int $age): string
    {
        $date = new DateTimeImmutable($effectiveFrom);
        return $date->modify('+' . $this->maturityMonths($age) . ' months')->format('Y-m-d');
    }

    public function getCoverage(int $memberId, string $personType, ?int $personId = null): ?array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM platinum_coverages
             WHERE member_id = :member_id
               AND covered_person_type = :person_type
               AND covered_person_id <=> :person_id
             ORDER BY id DESC LIMIT 1'
        );
        $statement->execute([
            ':member_id' => $memberId,
            ':person_type' => $personType,
            ':person_id' => $personId,
        ]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function remainingDays(int $coverageId, ?int $year = null, bool $lock = false): int
    {
        $year = $year ?? (int) date('Y');
        $sql = 'SELECT annual_limit, days_reserved, days_used
                FROM platinum_day_ledgers
                WHERE platinum_coverage_id = :coverage_id AND calendar_year = :year';
        if ($lock) {
            $sql .= ' FOR UPDATE';
        }

        $statement = $this->db->prepare($sql);
        $statement->execute([':coverage_id' => $coverageId, ':year' => $year]);
        $ledger = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$ledger) {
            return (int) ($this->config['annual_days_limit'] ?? 20);
        }

        return max(0, (int) $ledger['annual_limit'] - (int) $ledger['days_reserved'] - (int) $ledger['days_used']);
    }

    public function eligibility(int $coverageId, string $admissionDate, int $requestedDays): array
    {
        $statement = $this->db->prepare('SELECT * FROM platinum_coverages WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $coverageId]);
        $coverage = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$coverage) {
            return ['eligible' => false, 'reason' => 'Platinum coverage was not found.'];
        }
        if ($coverage['status'] !== 'active') {
            return ['eligible' => false, 'reason' => 'Platinum coverage is not active.'];
        }
        if (empty($coverage['maturity_date']) || $admissionDate < $coverage['maturity_date']) {
            return ['eligible' => false, 'reason' => 'Platinum coverage has not matured.'];
        }
        if (!empty($coverage['coverage_ends_at']) && $admissionDate > $coverage['coverage_ends_at']) {
            return ['eligible' => false, 'reason' => 'Admission is outside the active Platinum period.'];
        }
        if ($requestedDays < 1) {
            return ['eligible' => false, 'reason' => 'At least one inpatient day is required.'];
        }

        $remaining = $this->remainingDays($coverageId, (int) date('Y', strtotime($admissionDate)));
        if ($remaining < 1) {
            return ['eligible' => false, 'reason' => 'The annual Platinum day allowance has been exhausted.', 'remaining_days' => 0];
        }

        return [
            'eligible' => true,
            'remaining_days' => $remaining,
            'maximum_approval_days' => min($requestedDays, $remaining),
            'partial_approval_required' => $requestedDays > $remaining,
        ];
    }

    public function hasVerifiedPayment(int $memberId, float $amount, string $requestedAt, ?int $coverageId = null): bool
    {
        if ($coverageId) {
            $payment = $this->wrapper->fetch(
                "SELECT p.id FROM payments p
                 LEFT JOIN platinum_payment_allocations pa ON pa.payment_id = p.id AND pa.platinum_coverage_id = :coverage_id
                 WHERE p.member_id = :member_id AND p.status = 'completed'
                   AND (p.platinum_coverage_id = :coverage_id OR pa.allocated_amount >= :amount)
                 ORDER BY p.created_at ASC LIMIT 1",
                ['coverage_id' => $coverageId, 'member_id' => $memberId, 'amount' => $amount]
            );
            return !empty($payment);
        }
        $payment = $this->wrapper->fetch(
            "SELECT id FROM payments WHERE member_id = :member_id AND status = 'completed' AND amount >= :amount AND created_at >= :requested_at ORDER BY created_at ASC LIMIT 1",
            ['member_id' => $memberId, 'amount' => $amount, 'requested_at' => $requestedAt]
        );
        return !empty($payment);
    }

    public function approveInpatientRequest(int $requestId, int $adminId, ?int $approvedDays, ?string $notes, bool $overrideBalance = false, string $overrideReason = ''): array
    {
        $connection = $this->db;
        $connection->beginTransaction();
        try {
            $request = $this->wrapper->fetch('SELECT * FROM inpatient_requests WHERE id = :id FOR UPDATE', ['id' => $requestId]);
            if (!$request || !in_array($request['status'], ['submitted', 'under_review'], true)) {
                throw new RuntimeException('Inpatient request is unavailable for review.');
            }
            if ($approvedDays === null || $approvedDays < 1) {
                $this->wrapper->update('inpatient_requests', ['status' => 'rejected', 'rejection_reason' => $notes ?: 'Request was not approved.', 'reviewed_by' => $adminId, 'reviewed_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $requestId]);
                $connection->commit();
                return ['status' => 'rejected', 'approved_days' => 0];
            }
            (new ClaimEvidenceService())->assertReady('inpatient', $requestId);
            $approvedDays = min($approvedDays, (int) $request['requested_days']);
            $year = (int) date('Y', strtotime($request['admission_date']));
            $ledger = $this->wrapper->fetch('SELECT * FROM platinum_day_ledgers WHERE platinum_coverage_id = :coverage_id AND calendar_year = :year FOR UPDATE', ['coverage_id' => $request['platinum_coverage_id'], 'year' => $year]);
            if (!$ledger) {
                $this->wrapper->insert('platinum_day_ledgers', ['platinum_coverage_id' => $request['platinum_coverage_id'], 'calendar_year' => $year, 'annual_limit' => (int) ($this->config['annual_days_limit'] ?? 20)]);
                $ledger = $this->wrapper->fetch('SELECT * FROM platinum_day_ledgers WHERE platinum_coverage_id = :coverage_id AND calendar_year = :year FOR UPDATE', ['coverage_id' => $request['platinum_coverage_id'], 'year' => $year]);
            }
            $annualLimit = (int) $ledger['annual_limit'];
            if ($overrideBalance && $overrideReason !== '') {
                // Admin override: raise the annual limit just enough to honour the approved days beyond the normal balance.
                $shortfall = max(0, $approvedDays - max(0, $annualLimit - (int) $ledger['days_reserved'] - (int) $ledger['days_used']));
                if ($shortfall > 0) {
                    $annualLimit += $shortfall;
                    $this->wrapper->update('platinum_day_ledgers', ['annual_limit' => $annualLimit], 'id = :id', ['id' => $ledger['id']]);
                }
            } else {
                $remaining = max(0, $annualLimit - (int) $ledger['days_reserved'] - (int) $ledger['days_used']);
                $approvedDays = min($approvedDays, $remaining);
                if ($approvedDays < 1) {
                    throw new RuntimeException('No Platinum days remain for this calendar year.');
                }
            }
            $this->wrapper->update('platinum_day_ledgers', ['days_reserved' => (int) $ledger['days_reserved'] + $approvedDays], 'id = :id', ['id' => $ledger['id']]);
            $status = $approvedDays < (int) $request['requested_days'] ? 'partially_approved' : 'approved';
            $updateData = ['status' => $status, 'approved_days' => $approvedDays, 'reviewed_by' => $adminId, 'reviewed_at' => date('Y-m-d H:i:s'), 'admin_notes' => $notes];
            if ($overrideBalance && $overrideReason !== '') {
                $updateData['eligibility_override_reason'] = $overrideReason;
            }
            $this->wrapper->update('inpatient_requests', $updateData, 'id = :id', ['id' => $requestId]);
            $connection->commit();
            return ['status' => $status, 'approved_days' => $approvedDays];
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }
}
