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
                    COALESCE(d.full_name, c.label, CONCAT(u.first_name, " ", u.last_name)) AS covered_person_name
             FROM platinum_coverages pc
             JOIN members m ON m.id = pc.member_id
             JOIN users u ON u.id = m.user_id
             LEFT JOIN dependents d ON pc.covered_person_type = "dependent" AND d.id = pc.covered_person_id
             LEFT JOIN member_corporate_members c ON pc.covered_person_type = "corporate_member" AND c.id = pc.covered_person_id
             WHERE pc.status = "pending_approval" ORDER BY pc.requested_at ASC'
        );
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