<?php
/**
 * Payment Model - Handles payment processing and M-Pesa integration
 */
class Payment extends BaseModel 
{
    protected $table = 'payments';
    
    public function findByTransactionId($transactionId)
    {
        return $this->findAll(['transaction_id' => $transactionId]);
    }
    
    public function getMemberPayments($memberId, $limit = null)
    {
        $sql = "SELECT p.*, m.member_number 
                FROM {$this->table} p 
                JOIN members m ON p.member_id = m.id 
                WHERE p.member_id = :member_id 
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, ['member_id' => $memberId]);
    }

    public function getContributions($memberId, $limit = null)
    {
        $sql = "SELECT p.*, m.member_number 
                FROM {$this->table} p 
                JOIN members m ON p.member_id = m.id 
                WHERE p.member_id = :member_id 
                  AND p.payment_type != 'registration'
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, ['member_id' => $memberId]);
    }
    
    public function getPaymentsByDateRange($startDate, $endDate)
    {
        $sql = "SELECT p.*, m.member_number, u.first_name, u.last_name 
                FROM {$this->table} p 
                JOIN members m ON p.member_id = m.id 
                JOIN users u ON m.user_id = u.id 
                WHERE p.created_at BETWEEN :start_date AND :end_date 
                ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    public function getPendingPayments()
    {
        $sql = "SELECT p.*, m.member_number, u.first_name, u.last_name 
                FROM {$this->table} p 
                JOIN members m ON p.member_id = m.id 
                JOIN users u ON m.user_id = u.id 
                WHERE p.status = 'pending' 
                ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function recordPayment($data)
    {
        $requiredFields = ['member_id', 'amount', 'payment_method'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'pending';
        $data['reference'] = $data['reference'] ?? 'PAY-' . uniqid();
        
        return $this->create($data);
    }
    
    public function confirmPayment($paymentId, $transactionId = null)
    {
        $data = [
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($transactionId) {
            $data['transaction_id'] = $transactionId;
        }
        
        $this->update($paymentId, $data);

        // Apply membership-side effects for monthly contributions (coverage, status, grace period)
        $payment = $this->find($paymentId);
        if ($payment && isset($payment['payment_type']) && $payment['payment_type'] === 'monthly') {
            $memberModel = new Member();
            $memberModel->applySuccessfulMonthlyPayment(
                $payment['member_id'],
                $payment['payment_date'] ?? $payment['created_at'] ?? date('Y-m-d H:i:s')
            );
        }

        return true;
    }
    
    public function failPayment($paymentId, $reason = null)
    {
        return $this->update($paymentId, [
            'status' => 'failed',
            'failure_reason' => $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function getMonthlyPaymentStatus($memberId, $year, $month)
    {
        $sql = "SELECT 
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_payments,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as paid_amount
                FROM {$this->table} 
                WHERE member_id = :member_id 
                AND YEAR(created_at) = :year 
                AND MONTH(created_at) = :month";
        
        return $this->db->fetch($sql, [
            'member_id' => $memberId,
            'year' => $year,
            'month' => $month
        ]);
    }
    
    public function getDefaultedMembers()
    {
        $sql = "SELECT DISTINCT 
                    m.id, m.member_number, 
                    u.first_name, u.last_name, u.email,
                    COUNT(p.id) as missed_payments,
                    SUM(p.amount) as outstanding_amount
                FROM members m 
                JOIN users u ON m.user_id = u.id 
                LEFT JOIN {$this->table} p ON m.id = p.member_id 
                    AND p.status = 'pending' 
                    AND p.created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
                WHERE m.status = 'active'
                GROUP BY m.id, m.member_number, u.first_name, u.last_name, u.email
                HAVING missed_payments > 0";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getTotalRevenue($startDate = null, $endDate = null)
    {
        $sql = "SELECT SUM(amount) as total_revenue 
                FROM {$this->table} 
                WHERE status = 'completed'";
        
        $params = [];
        
        if ($startDate) {
            $sql .= " AND created_at >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND created_at <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['total_revenue'] ?? 0;
    }
    
    public function getPaymentStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_payments,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    public function getTotalPayments()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    public function getMonthlyRevenue()
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as revenue
                FROM {$this->table} 
                WHERE status = 'completed' 
                AND YEAR(created_at) = YEAR(CURDATE()) 
                AND MONTH(created_at) = MONTH(CURDATE())";
        
        $result = $this->db->fetch($sql);
        return $result['revenue'] ?? 0;
    }
    
    public function getMembersWithOverduePayments()
    {
        $sql = "SELECT DISTINCT m.id 
                FROM members m 
                LEFT JOIN {$this->table} p ON m.id = p.member_id 
                    AND p.status = 'completed' 
                    AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                WHERE m.status = 'active' 
                AND p.id IS NULL";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getRecentPayments($limit = 10)
    {
        $sql = "SELECT p.*, m.member_number, u.first_name, u.last_name 
                FROM {$this->table} p 
                JOIN members m ON p.member_id = m.id 
                JOIN users u ON m.user_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, ['limit' => $limit]);
    }
    
    public function getPaymentWithDetails($id)
    {
        $sql = "SELECT p.*, m.member_number, u.first_name, u.last_name, u.email, u.phone
                FROM {$this->table} p
                JOIN members m ON p.member_id = m.id
                JOIN users u ON m.user_id = u.id
                WHERE p.id = :id
                LIMIT 1";
        return $this->db->fetch($sql, ['id' => $id]);
    }

    public function getAllPaymentsWithDetails($conditions = [])
    {
        $sql = "SELECT 
                    p.*,
                    m.member_number,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM {$this->table} p
                JOIN members m ON p.member_id = m.id
                JOIN users u ON m.user_id = u.id";
        
        $params = [];
        $where_clauses = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                if ($field === 'member_id') {
                    $where_clauses[] = "p.member_id = :member_id";
                    $params['member_id'] = (int)$value;
                }
                if ($field === 'status' && $value !== 'all') {
                    $where_clauses[] = "p.status = :status";
                    $params['status'] = $value;
                }
                if ($field === 'start_date') {
                    $where_clauses[] = "p.created_at >= :start_date";
                    $params['start_date'] = $value;
                }
                if ($field === 'end_date') {
                    $where_clauses[] = "p.created_at <= :end_date";
                    $params['end_date'] = $value;
                }
            }
        }
        
        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getPaymentsByMethod($startDate, $endDate)
    {
        $sql = "SELECT 
                    payment_method,
                    COUNT(*) as count,
                    SUM(amount) as total_amount
                FROM {$this->table}
                WHERE created_at BETWEEN :start_date AND :end_date
                AND status = 'completed'
                GROUP BY payment_method";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    public function getPaymentsByType($startDate, $endDate)
    {
        $sql = "SELECT 
                    payment_type,
                    COUNT(*) as count,
                    SUM(amount) as total_amount
                FROM {$this->table}
                WHERE created_at BETWEEN :start_date AND :end_date
                AND status = 'completed'
                GROUP BY payment_type";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    public function getFailedPayments()
    {
        $sql = "SELECT p.*, m.member_number, u.first_name, u.last_name 
                FROM {$this->table} p 
                JOIN members m ON p.member_id = m.id 
                JOIN users u ON m.user_id = u.id 
                WHERE p.status = 'failed' 
                ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getPaymentReport($startDate, $endDate)
    {
        $sql = "SELECT 
                    DATE(p.created_at) as payment_date,
                    COUNT(*) as total_payments,
                    SUM(p.amount) as total_amount,
                    SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END) as completed_amount,
                    COUNT(CASE WHEN p.status = 'completed' THEN 1 END) as completed_count,
                    COUNT(CASE WHEN p.status = 'failed' THEN 1 END) as failed_count
                FROM {$this->table} p
                WHERE p.created_at BETWEEN :start_date AND :end_date
                GROUP BY DATE(p.created_at)
                ORDER BY payment_date DESC";
        
        return $this->db->fetchAll($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    /**
     * Get total count of contributions/payments
     * 
     * @return int Total contribution count
     */
    public function getContributionCount()
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE status = 'completed'";
        
        $result = $this->db->fetch($sql);
        return $result ? (int)$result['count'] : 0;
    }
}
