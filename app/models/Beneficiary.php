<?php
/**
 * Beneficiary Model - Handles member beneficiaries
 */
class Beneficiary extends BaseModel 
{
    protected $table = 'beneficiaries';
    
    public function getMemberBeneficiaries($memberId)
    {
        return $this->findAll(['member_id' => $memberId], 'created_at ASC');
    }
    
    public function addBeneficiary($data)
    {
        $beneficiaryData = [
            'member_id' => $data['member_id'],
            'full_name' => $data['full_name'],
            'relationship' => $data['relationship'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'id_number' => ($data['id_number'] ?? '') !== '' ? $data['id_number'] : null,
            'phone_number' => $data['phone_number'] ?? null,
            'percentage' => $data['percentage'] ?? 100,
            'is_active' => 1
        ];
        
        return $this->create($beneficiaryData);
    }
    
    public function updateBeneficiary($id, $data)
    {
        $allowedFields = ['full_name', 'relationship', 'date_of_birth', 'id_number', 'phone_number', 'percentage'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        return $this->update($id, $updateData);
    }
    
    public function deactivateBeneficiary($id)
    {
        return $this->update($id, ['is_active' => 0]);
    }
    
    public function activateBeneficiary($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }
    
    public function validateBeneficiaryPercentages($memberId, $excludeId = null)
    {
        $sql = "SELECT SUM(percentage) as total_percentage 
                FROM {$this->table} 
                WHERE member_id = :member_id 
                AND is_active = 1";
        
        $params = ['member_id' => $memberId];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['total_percentage'] ?? 0;
    }
    
    public function getActiveBeneficiaries($memberId)
    {
        return $this->findAll([
            'member_id' => $memberId,
            'is_active' => 1
        ], 'created_at ASC');
    }
}
