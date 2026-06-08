<?php
/**
 * Claim Model - Handles funeral claim processing and management
 */
class Claim extends BaseModel 
{
    protected $table = 'claims';
    protected $columnCache = null;

    private function getTableColumns()
    {
        if ($this->columnCache !== null) {
            return $this->columnCache;
        }

        $columns = $this->db->fetchAll("DESCRIBE {$this->table}");
        $this->columnCache = array_map(static function ($column) {
            return $column['Field'];
        }, $columns);

        return $this->columnCache;
    }

    private function mapLegacyColumns(array $data)
    {
        $columns = $this->getTableColumns();

        if (!in_array('service_delivery_type', $columns, true) && in_array('settlement_type', $columns, true)) {
            $data['settlement_type'] = ($data['service_delivery_type'] ?? 'standard_services') === 'cash_alternative'
                ? 'cash'
                : 'services';
        }

        if (!in_array('cash_alternative_amount', $columns, true) && in_array('cash_settlement_amount', $columns, true)) {
            if (isset($data['cash_alternative_amount'])) {
                $data['cash_settlement_amount'] = $data['cash_alternative_amount'];
            }
        }

        return $data;
    }

    private function filterDataToColumns(array $data)
    {
        $columns = $this->getTableColumns();
        return array_intersect_key($data, array_flip($columns));
    }
    
    public function getMemberClaims($memberId)
    {
        $sql = "SELECT c.*, m.member_number, u.first_name, u.last_name,
                       b.relationship, b.full_name as beneficiary_name
                FROM {$this->table} c
                JOIN members m ON c.member_id = m.id
                JOIN users u ON m.user_id = u.id
                LEFT JOIN beneficiaries b ON c.beneficiary_id = b.id
                WHERE c.member_id = :member_id
                ORDER BY c.created_at DESC";

        return $this->db->fetchAll($sql, ['member_id' => $memberId]);
    }
    
    public function getClaimsByStatus($status)
    {
        $sql = "SELECT c.*, m.member_number, u.first_name, u.last_name 
                FROM {$this->table} c 
                JOIN members m ON c.member_id = m.id 
                JOIN users u ON m.user_id = u.id 
                WHERE c.status = :status 
                ORDER BY c.created_at DESC";
        
        return $this->db->fetchAll($sql, ['status' => $status]);
    }
    
    public function getClaimsByDateRange($startDate, $endDate)
    {
        $sql = "SELECT c.*, m.member_number, u.first_name, u.last_name 
                FROM {$this->table} c 
                JOIN members m ON c.member_id = m.id 
                JOIN users u ON m.user_id = u.id 
                WHERE c.created_at BETWEEN :start_date AND :end_date 
                ORDER BY c.created_at DESC";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    public function submitClaim($data)
    {
        // Service-based claim submission per SHENA Companion Policy 2026
        // Required fields: deceased details and mortuary information
        $requiredFields = ['member_id', 'deceased_name', 'deceased_id_number', 'date_of_death', 'place_of_death'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        // Set deceased type (member or dependent)
        if (!isset($data['deceased_type'])) {
            $data['deceased_type'] = isset($data['dependent_id']) && $data['dependent_id'] ? 'dependent' : 'member';
        }
        
        // Validate dependent exists if claiming for dependent
        if ($data['deceased_type'] === 'dependent' && isset($data['dependent_id'])) {
            $dependentModel = new Dependent();
            $dependent = $dependentModel->find($data['dependent_id']);
            if (!$dependent || $dependent['member_id'] != $data['member_id']) {
                throw new Exception("Invalid dependent for this member");
            }
            if (!$dependent['is_covered']) {
                throw new Exception("Dependent is not currently covered");
            }
        }
        
        // Set service delivery defaults
        $data['service_delivery_type'] = $data['service_delivery_type'] ?? 'standard_services';
        $data['mortuary_days_count'] = $data['mortuary_days_count'] ?? 0;

        // For service-based claims, set claim_amount to 0 (services provided instead of cash)
        $data['claim_amount'] = 0.00;

        // Validate mortuary days (max 14 per policy)
        if (isset($data['mortuary_days_count']) && $data['mortuary_days_count'] > 14) {
            throw new Exception("Mortuary preservation coverage limited to 14 days per policy");
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = 'submitted';

        $data = $this->mapLegacyColumns($data);
        $data = $this->filterDataToColumns($data);

        $claimId = $this->create($data);
        
        // Initialize service checklist for standard services (if table exists)
        if (($data['service_delivery_type'] ?? 'standard_services') === 'standard_services') {
            try {
                if (class_exists('ClaimServiceChecklist')) {
                    $checklistModel = new ClaimServiceChecklist();
                    $checklistModel->initializeChecklist($claimId);
                }
            } catch (Exception $e) {
                // Log but don't fail the claim submission if checklist fails
                error_log('Failed to initialize service checklist: ' . $e->getMessage());
            }
        }
        
        return $claimId;
    }
    
    /**
     * Approve claim for standard service delivery
     * Per policy: Mortuary bill, body dressing, coffin, transportation, equipment
     */
    public function approveClaimForServices($claimId, $deliveryDate = null, $notes = null)
    {
        $data = [
            'status' => 'approved',
            'service_delivery_type' => 'standard_services',
            'services_delivery_date' => $deliveryDate ?? date('Y-m-d'),
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($notes) {
            $data['processing_notes'] = $notes;
        }
        
        return $this->update($claimId, $data);
    }
    
    /**
     * Approve claim for cash alternative (KSH 20,000)
     * Per policy Section 12: Only in exceptional circumstances with mutual agreement
     */
    public function approveClaimForCashAlternative($claimId, $reason, $requestedBy, $approvedBy)
    {
        // Validate request
        $cashAltModel = new ClaimCashAlternative();
        $validation = $cashAltModel->validateRequest($claimId, $reason, $requestedBy);
        
        if ($validation !== true) {
            throw new Exception("Validation failed: " . implode(", ", $validation));
        }
        
        // Update claim
        $data = [
            'status' => 'approved',
            'service_delivery_type' => 'cash_alternative',
            'cash_alternative_amount' => 20000.00,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->update($claimId, $data);
        
        // Create cash alternative agreement
        $cashAltModel->createAgreement([
            'claim_id' => $claimId,
            'reason_category' => $this->determineReasonCategory($reason),
            'detailed_reason' => $reason,
            'requested_by' => $requestedBy,
            'approved_by' => $approvedBy,
            'amount_paid' => 20000.00
        ]);
        
        return true;
    }
    
    /**
     * Legacy method - kept for backward compatibility
     */
    public function approveClaim($claimId, $approvedAmount = null, $notes = null)
    {
        // Default to service delivery
        return $this->approveClaimForServices($claimId, null, $notes);
    }
    
    /**
     * Update service delivery status
     */
    public function updateServiceDeliveryStatus($claimId, $serviceType, $completed = true)
    {
        $validServices = ['mortuary_bill_settled', 'body_dressing_completed', 'coffin_delivered', 
                         'transportation_arranged', 'equipment_delivered'];
        
        if (!in_array($serviceType, $validServices)) {
            throw new Exception("Invalid service type: {$serviceType}");
        }
        
        return $this->update($claimId, [$serviceType => $completed]);
    }
    
    /**
     * Get claim service delivery checklist
     */
    public function getClaimServiceChecklist($claimId)
    {
        $checklistModel = new ClaimServiceChecklist();
        return $checklistModel->getClaimChecklist($claimId);
    }
    
    /**
     * Complete claim after all services delivered
     */
    public function completeClaim($claimId, $completionNotes = null)
    {
        $claim = $this->find($claimId);
        
        if (!$claim) {
            throw new Exception("Claim not found");
        }
        
        // Check if cash alternative
        if ($claim['service_delivery_type'] === 'cash_alternative') {
            $cashAltModel = new ClaimCashAlternative();
            $agreement = $cashAltModel->getByClaimId($claimId);
            
            if (!$agreement || !$agreement['agreement_signed']) {
                throw new Exception("Cash alternative agreement must be signed before completion");
            }
            
            if (!$agreement['paid_at']) {
                throw new Exception("Cash payment must be processed before completion");
            }
        } else {
            // Check if all services completed
            $checklistModel = new ClaimServiceChecklist();
            if (!$checklistModel->areAllServicesCompleted($claimId)) {
                throw new Exception("All services must be completed before marking claim as completed");
            }
        }
        
        $data = [
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($completionNotes) {
            $data['processing_notes'] = ($claim['processing_notes'] ?? '') . "\\n" . $completionNotes;
        }
        
        return $this->update($claimId, $data);
    }
    
    /**
     * Determine reason category for cash alternative
     */
    private function determineReasonCategory($reason)
    {
        $reason = strtolower($reason);
        
        if (strpos($reason, 'security') !== false || strpos($reason, 'risk') !== false) {
            return 'security_risk';
        } elseif (strpos($reason, 'client') !== false || strpos($reason, 'request') !== false) {
            return 'client_request';
        } elseif (strpos($reason, 'logistic') !== false || strpos($reason, 'transport') !== false) {
            return 'logistical_issue';
        }
        
        return 'other';
    }
    
    public function rejectClaim($claimId, $reason)
    {
        return $this->update($claimId, [
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function processClaim($claimId, $paymentReference = null)
    {
        $data = [
            'status' => 'processed',
            'processed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($paymentReference) {
            $data['payment_reference'] = $paymentReference;
        }
        
        return $this->update($claimId, $data);
    }
    
    public function getClaimStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_claims,
                    COUNT(CASE WHEN status IN ('submitted', 'under_review') THEN 1 END) as pending_claims,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_claims,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_claims,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as processed_claims,
                    SUM(claim_amount) as total_claimed_amount,
                    SUM(CASE WHEN status IN ('approved', 'completed') THEN approved_amount ELSE 0 END) as total_approved_amount
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    public function getPendingClaimsCount()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'submitted'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    public function getApprovedClaimsCount()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'approved'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    public function getTotalClaimsValue()
    {
        $sql = "SELECT COALESCE(SUM(approved_amount), 0) as total_value
                FROM {$this->table} 
                WHERE status IN ('approved', 'paid')";
        
        $result = $this->db->fetch($sql);
        return $result['total_value'] ?? 0;
    }
    
    public function getPendingClaims()
    {
        $sql = "SELECT c.*, m.member_number, u.first_name, u.last_name 
                FROM {$this->table} c 
                JOIN members m ON c.member_id = m.id 
                JOIN users u ON m.user_id = u.id 
                WHERE c.status IN ('submitted', 'under_review')
                ORDER BY c.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get the latest pending claim (for emergency alert)
     */
    public function getLatestPendingClaim()
    {
        $sql = "SELECT c.*, 
                       m.member_number,
                       CONCAT(u.first_name, ' ', u.last_name) as member_name,
                       CONCAT(au.first_name, ' ', au.last_name) as agent_name
                FROM {$this->table} c 
                JOIN members m ON c.member_id = m.id 
                JOIN users u ON m.user_id = u.id
                LEFT JOIN agents a ON m.agent_id = a.id
                LEFT JOIN users au ON a.user_id = au.id
                WHERE c.status IN ('submitted', 'under_review')
                ORDER BY c.created_at DESC
                LIMIT 1";
        
        return $this->db->fetch($sql);
    }
    
    public function getApprovedClaims()
    {
        $sql = "SELECT c.*, m.member_number, u.first_name, u.last_name 
                FROM {$this->table} c 
                JOIN members m ON c.member_id = m.id 
                JOIN users u ON m.user_id = u.id 
                WHERE c.status = 'approved' 
                ORDER BY c.approved_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getProcessedClaims()
    {
        $sql = "SELECT c.*, m.member_number, u.first_name, u.last_name 
                FROM {$this->table} c 
                JOIN members m ON c.member_id = m.id 
                JOIN users u ON m.user_id = u.id 
                WHERE c.status = 'processed' 
                ORDER BY c.processed_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getRecentClaims($limit = 10)
    {
        $sql = "SELECT c.*, m.member_number, u.first_name, u.last_name 
                FROM {$this->table} c 
                JOIN members m ON c.member_id = m.id 
                JOIN users u ON m.user_id = u.id 
                ORDER BY c.created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, ['limit' => $limit]);
    }
    
    public function getClaimDetails($claimId)
    {
        $sql = "SELECT 
                    c.*,
                    m.member_number,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM {$this->table} c
                JOIN members m ON c.member_id = m.id
                JOIN users u ON m.user_id = u.id
                WHERE c.id = :claim_id";
        
        return $this->db->fetch($sql, ['claim_id' => $claimId]);
    }
    
    public function getAllClaimsWithDetails($conditions = [])
    {
        $sql = "SELECT 
                    c.*,
                    m.member_number,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM {$this->table} c
                JOIN members m ON c.member_id = m.id
                JOIN users u ON m.user_id = u.id";
        
        $params = [];
        $where_clauses = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                if ($field === 'status' && $value !== 'all') {
                    $where_clauses[] = "c.status = :status";
                    $params['status'] = $value;
                }
                if ($field === 'start_date') {
                    $where_clauses[] = "c.created_at >= :start_date";
                    $params['start_date'] = $value;
                }
                if ($field === 'end_date') {
                    $where_clauses[] = "c.created_at <= :end_date";
                    $params['end_date'] = $value;
                }
            }
        }
        
        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getClaimReport($startDate, $endDate)
    {
        $sql = "SELECT 
                    DATE(c.created_at) as claim_date,
                    COUNT(*) as total_claims,
                    SUM(c.claim_amount) as total_amount,
                    SUM(CASE WHEN c.status = 'approved' OR c.status = 'processed' THEN c.approved_amount ELSE 0 END) as approved_amount,
                    COUNT(CASE WHEN c.status = 'approved' THEN 1 END) as approved_count,
                    COUNT(CASE WHEN c.status = 'rejected' THEN 1 END) as rejected_count
                FROM {$this->table} c
                WHERE c.created_at BETWEEN :start_date AND :end_date
                GROUP BY DATE(c.created_at)
                ORDER BY claim_date DESC";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    public function getMonthlyClaims()
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count,
                    SUM(claim_amount) as total_amount
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get claim with dependent information
     * 
     * @param int $claimId Claim ID
     * @return array|null Claim details with dependent info
     */
    public function getClaimWithDependent($claimId)
    {
        $sql = "SELECT c.*, m.member_number, u.first_name, u.last_name, u.email, u.phone,
                       d.full_name as dependent_name, d.relationship as dependent_relationship,
                       d.date_of_birth as dependent_dob
                FROM {$this->table} c 
                JOIN members m ON c.member_id = m.id 
                JOIN users u ON m.user_id = u.id
                LEFT JOIN dependents d ON c.dependent_id = d.id
                WHERE c.id = :claim_id";
        
        return $this->db->fetch($sql, ['claim_id' => $claimId]);
    }
    
    /**
     * Get all claims with dependent information
     * 
     * @param array $conditions Filter conditions
     * @return array List of claims
     */
    public function getAllClaimsWithDependents($conditions = [])
    {
        $sql = "SELECT c.*, m.member_number, u.first_name, u.last_name,
                       d.full_name as dependent_name, d.relationship as dependent_relationship
                FROM {$this->table} c 
                JOIN members m ON c.member_id = m.id 
                JOIN users u ON m.user_id = u.id
                LEFT JOIN dependents d ON c.dependent_id = d.id";
        
        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $column => $value) {
                $where_clauses[] = "c.{$column} = :{$column}";
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        return $this->db->fetchAll($sql, $conditions);
    }
}
