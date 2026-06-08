<?php
/**
 * Corporate members attached to a primary member account for billing.
 */
class MemberCorporateMember extends BaseModel
{
    protected $table = 'member_corporate_members';

    public function getActiveForMember($memberId)
    {
        return $this->findAll([
            'member_id' => (int)$memberId,
            'status' => 'active'
        ], 'id ASC');
    }

    public function replaceForMember($memberId, array $items)
    {
        $memberId = (int)$memberId;
        $this->db->delete($this->table, 'member_id = :member_id', ['member_id' => $memberId]);

        foreach ($items as $item) {
            $packageKey = trim((string)($item['package_key'] ?? ''));
            if ($packageKey === '') {
                continue;
            }

            $this->create([
                'member_id' => $memberId,
                'label' => trim((string)($item['label'] ?? '')),
                'relationship' => trim((string)($item['relationship'] ?? 'corporate')),
                'package_key' => $packageKey,
                'package_name' => trim((string)($item['package_name'] ?? $packageKey)),
                'monthly_contribution' => (float)($item['monthly_contribution'] ?? 0),
                'status' => $item['status'] ?? 'active',
            ]);
        }

        return true;
    }

    public function sumActiveForMember($memberId)
    {
        $row = $this->db->fetch(
            "SELECT COALESCE(SUM(monthly_contribution), 0) AS total
             FROM {$this->table}
             WHERE member_id = :member_id AND status = 'active'",
            ['member_id' => (int)$memberId]
        );

        return (float)($row['total'] ?? 0);
    }
}
