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
        $existing = $this->getActiveForMember($memberId);
        $existingById = [];
        foreach ($existing as $row) {
            $existingById[(int)$row['id']] = $row;
        }
        $keptIds = [];

        foreach ($items as $item) {
            $packageKey = trim((string)($item['package_key'] ?? ''));
            if ($packageKey === '') {
                continue;
            }

            $data = [
                'member_id' => $memberId,
                'label' => trim((string)($item['label'] ?? '')),
                'relationship' => trim((string)($item['relationship'] ?? 'corporate')),
                'date_of_birth' => !empty($item['date_of_birth']) ? $item['date_of_birth'] : null,
                'package_key' => $packageKey,
                'package_name' => trim((string)($item['package_name'] ?? $packageKey)),
                'monthly_contribution' => (float)($item['monthly_contribution'] ?? 0),
                'status' => $item['status'] ?? 'active',
            ];
            $itemId = max(0, (int)($item['id'] ?? 0));
            if ($itemId > 0 && isset($existingById[$itemId])) {
                $this->update($itemId, $data);
                $keptIds[$itemId] = true;
            } else {
                $this->create($data);
            }
        }

        foreach ($existingById as $existingId => $_existing) {
            if (isset($keptIds[$existingId])) {
                continue;
            }
            // A removed corporate group can no longer keep an orphaned Platinum cover.
            $this->db->update('platinum_coverages', [
                'status' => 'cancelled',
                'coverage_ends_at' => date('Y-m-d'),
            ], "member_id = :member_id AND covered_person_type = 'corporate_member' AND covered_person_id = :covered_person_id AND status = 'active'", [
                'member_id' => $memberId,
                'covered_person_id' => $existingId,
            ]);
            $this->delete($existingId);
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
