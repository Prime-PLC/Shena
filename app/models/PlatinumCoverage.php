<?php

class PlatinumCoverage extends BaseModel
{
    protected $table = 'platinum_coverages';

    public function forMember(int $memberId): array
    {
        return $this->db->fetchAll(
            'SELECT pc.*, CASE pc.covered_person_type
                WHEN "principal" THEN CONCAT(u.first_name, " ", u.last_name)
                WHEN "dependent" THEN d.full_name
                WHEN "corporate_member" THEN c.label
                END AS covered_person_name
             FROM platinum_coverages pc
             JOIN members m ON m.id = pc.member_id
             JOIN users u ON u.id = m.user_id
             LEFT JOIN dependents d ON pc.covered_person_type = "dependent" AND d.id = pc.covered_person_id
             LEFT JOIN member_corporate_members c ON pc.covered_person_type = "corporate_member" AND c.id = pc.covered_person_id
             WHERE pc.member_id = :member_id ORDER BY pc.created_at DESC',
            ['member_id' => $memberId]
        );
    }

    public function getCoverage(int $memberId, string $personType, ?int $personId = null): ?array
    {
        $result = $this->db->fetch('SELECT * FROM platinum_coverages WHERE member_id = :member_id AND covered_person_type = :type AND covered_person_id <=> :person_id ORDER BY id DESC LIMIT 1', ['member_id' => $memberId, 'type' => $personType, 'person_id' => $personId]);
        return $result ?: null;
    }

    public function pending(): array
    {
        return $this->db->fetchAll(
            'SELECT pc.*, m.member_number, u.first_name, u.last_name,
                    COALESCE(d.full_name, c.label, CONCAT(u.first_name, " ", u.last_name)) AS covered_person_name,
                    COALESCE(pc.package_name, c.package_name, pc.package_key, c.package_key) AS package_display_name
             FROM platinum_coverages pc
             JOIN members m ON m.id = pc.member_id
             JOIN users u ON u.id = m.user_id
             LEFT JOIN dependents d ON pc.covered_person_type = "dependent" AND d.id = pc.covered_person_id
             LEFT JOIN member_corporate_members c ON pc.covered_person_type = "corporate_member" AND c.id = pc.covered_person_id
             WHERE pc.status = "pending_approval" ORDER BY pc.requested_at ASC'
        );
    }

    /**
     * Members that carry at least one Platinum coverage, with their covered people.
     * Used by the admin portal to scope Platinum-only workflows (e.g. creating an
     * inpatient request) to members who actually hold the add-on.
     *
     * @param bool $activeOnly Restrict to members with an active coverage.
     */
    public function membersWithPlatinum(bool $activeOnly = false): array
    {
        $statusClause = $activeOnly ? 'AND pc.status = "active"' : 'AND pc.status IN ("pending_approval", "active")';

        $rows = $this->db->fetchAll(
            'SELECT pc.id AS coverage_id, pc.member_id, pc.covered_person_type, pc.covered_person_id,
                    pc.status, pc.monthly_contribution, pc.maturity_date,
                    m.member_number, u.first_name, u.last_name,
                    COALESCE(d.full_name, c.label, CONCAT(u.first_name, " ", u.last_name)) AS covered_person_name
             FROM platinum_coverages pc
             JOIN members m ON m.id = pc.member_id
             JOIN users u ON u.id = m.user_id
             LEFT JOIN dependents d ON pc.covered_person_type = "dependent" AND d.id = pc.covered_person_id
             LEFT JOIN member_corporate_members c ON pc.covered_person_type = "corporate_member" AND c.id = pc.covered_person_id
             WHERE 1 = 1 ' . $statusClause . '
             ORDER BY u.first_name ASC, u.last_name ASC, pc.id ASC'
        );

        $members = [];
        foreach ($rows as $row) {
            $memberId = (int) $row['member_id'];
            if (!isset($members[$memberId])) {
                $members[$memberId] = [
                    'member_id' => $memberId,
                    'member_number' => $row['member_number'],
                    'name' => trim($row['first_name'] . ' ' . $row['last_name']),
                    'coverages' => [],
                ];
            }
            $members[$memberId]['coverages'][] = [
                'coverage_id' => (int) $row['coverage_id'],
                'covered_person_name' => $row['covered_person_name'],
                'covered_person_type' => $row['covered_person_type'],
                'status' => $row['status'],
                'monthly_contribution' => (float) $row['monthly_contribution'],
                'maturity_date' => $row['maturity_date'],
            ];
        }

        return array_values($members);
    }

    /**
     * Member IDs holding Platinum, keyed by member id, for cheap tier tagging of
     * member/payment listings without an N+1 query per row.
     *
     * @return array<int, string> member_id => highest-precedence coverage status
     */
    public function tierMapByMember(): array
    {
        $rows = $this->db->fetchAll(
            'SELECT member_id,
                    MAX(status = "active") AS has_active,
                    COUNT(*) AS coverage_count
             FROM platinum_coverages
             WHERE status IN ("pending_approval", "active")
             GROUP BY member_id'
        );

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['member_id']] = !empty($row['has_active']) ? 'active' : 'pending';
        }

        return $map;
    }

    /** Current group state for member-management and pre-claim decisions. */
    public function accountSummariesByMember(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT pc.member_id, pc.id, pc.status, pc.monthly_contribution, pc.maturity_date,
                    COALESCE(pc.package_name, pc.package_key, pc.covered_person_type) AS group_name,
                    GREATEST(0, 20 - COALESCE(l.days_reserved, 0) - COALESCE(l.days_used, 0)) AS remaining_days,
                    COALESCE(a.paid_amount, 0) AS current_month_paid
             FROM platinum_coverages pc
             LEFT JOIN platinum_day_ledgers l ON l.platinum_coverage_id = pc.id AND l.calendar_year = YEAR(CURDATE())
             LEFT JOIN (
                SELECT platinum_coverage_id, SUM(allocated_amount) AS paid_amount
                FROM platinum_payment_allocations
                WHERE allocation_month = DATE_FORMAT(CURDATE(), '%Y-%m-01')
                GROUP BY platinum_coverage_id
             ) a ON a.platinum_coverage_id = pc.id
             WHERE pc.status = 'active'
             ORDER BY pc.member_id, pc.id"
        );
        $summary = [];
        foreach ($rows as $row) {
            $row['current_month_deficit'] = max(0, (float) $row['monthly_contribution'] - (float) $row['current_month_paid']);
            $summary[(int) $row['member_id']][] = $row;
        }
        return $summary;
    }

    public function approve(int $id, int $adminId): void
    {
        $this->db->query(
            'UPDATE platinum_coverages SET status = "active", approved_at = NOW(), approved_by = :admin_id,
             effective_from = COALESCE(effective_from, CURDATE()),
             maturity_date = COALESCE(maturity_date, DATE_ADD(CURDATE(), INTERVAL maturity_months MONTH))
             WHERE id = :id AND status = "pending_approval"',
            ['admin_id' => $adminId, 'id' => $id]
        );
    }

    /**
     * Approve a coverage that has not met the normal payment/eligibility bar, with a mandatory audit reason.
     */
    public function approveWithOverride(int $id, int $adminId, string $reason): void
    {
        $this->db->query(
            'UPDATE platinum_coverages SET status = "active", approved_at = NOW(), approved_by = :admin_id,
             activation_method = "admin_override", override_reason = :reason, override_by = :admin_id, override_at = NOW(),
             effective_from = COALESCE(effective_from, CURDATE()),
             maturity_date = COALESCE(maturity_date, DATE_ADD(CURDATE(), INTERVAL maturity_months MONTH))
             WHERE id = :id AND status = "pending_approval"',
            ['admin_id' => $adminId, 'id' => $id, 'reason' => $reason]
        );
    }

    public function reject(int $id, int $adminId): void
    {
        $this->db->query(
            'UPDATE platinum_coverages SET status = "rejected", approved_at = NOW(), approved_by = :admin_id
             WHERE id = :id AND status = "pending_approval"',
            ['admin_id' => $adminId, 'id' => $id]
        );
    }
}
