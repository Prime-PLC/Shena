<?php
/**
 * Agent Model
 * Manages agent/representative accounts, registrations, and commissions
 * 
 * @package Shena\Models
 */

class Agent extends BaseModel
{
    protected $table = 'agents';
    
    /**
     * Create a new agent account
     * 
     * @param array $data Agent data including user_id, first_name, last_name, etc.
     * @return int|false Agent ID or false on failure
     */
    public function createAgent($data)
    {
        // Generate agent number (e.g., AG20240001)
        $agentNumber = $this->generateAgentNumber();
        
        $sql = "INSERT INTO agents (
                    user_id, agent_number, first_name, last_name, 
                    national_id, phone, email, address, county,
                    commission_rate, registration_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";
        
        $params = [
            $data['user_id'],
            $agentNumber,
            $data['first_name'],
            $data['last_name'],
            $data['national_id'],
            $data['phone'],
            $data['email'],
            $data['address'] ?? null,
            $data['county'] ?? null,
            $data['commission_rate'] ?? 10.00
        ];
        
        $stmt = $this->db->query($sql, $params);
        if ($stmt) {
            return $this->db->getConnection()->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Generate unique agent number
     * Format: AGYYYYNNNN (AG + Year + Sequential Number)
     * 
     * @return string Agent number
     */
    private function generateAgentNumber()
    {
        $year = date('Y');
        $prefix = 'AG' . $year;
        
        // Get the last agent number for this year
        $sql = "SELECT agent_number FROM agents 
                WHERE agent_number LIKE ? 
                ORDER BY agent_number DESC 
                LIMIT 1";
        
        $lastAgent = $this->db->query($sql, [$prefix . '%'])->fetch();
        
        if ($lastAgent) {
            // Extract sequence number and increment
            $lastNumber = (int)substr($lastAgent['agent_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get agent by ID
     * 
     * @param int $agentId Agent ID
     * @return array|false Agent data or false
     */
    public function getAgentById($agentId)
    {
        $sql = "SELECT a.*, u.email as user_email 
                FROM agents a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = ?";
        
        return $this->db->query($sql, [$agentId])->fetch();
    }
    
    /**
     * Get agent by user ID
     * 
     * @param int $userId User ID
     * @return array|false Agent data or false
     */
    public function getAgentByUserId($userId)
    {
        $sql = "SELECT * FROM agents WHERE user_id = ?";
        return $this->db->query($sql, [$userId])->fetch();
    }
    
    /**
     * Get agent by agent number
     * 
     * @param string $agentNumber Agent number
     * @return array|false Agent data or false
     */
    public function getAgentByNumber($agentNumber)
    {
        $sql = "SELECT * FROM agents WHERE agent_number = ?";
        return $this->db->query($sql, [$agentNumber])->fetch();
    }
    
    /**
     * Get all agents with optional filtering
     * 
     * @param array $filters Optional filters (status, search, etc.)
     * @param int $limit Optional result limit
     * @param int $offset Optional result offset
     * @return array List of agents
     */
    public function getAllAgents($filters = [], $limit = 50, $offset = 0)
    {
        $sql = "SELECT a.*, 
                       COUNT(DISTINCT m.id) as total_members,
                       SUM(CASE WHEN ac.status = 'pending' THEN ac.commission_amount ELSE 0 END) as pending_commission,
                       SUM(CASE WHEN ac.status = 'paid' THEN ac.commission_amount ELSE 0 END) as paid_commission
                FROM agents a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN members m ON a.id = m.agent_id
                LEFT JOIN agent_commissions ac ON a.id = ac.agent_id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.agent_number LIKE ? OR a.phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " GROUP BY a.id ORDER BY a.created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = max(1, (int)$limit);
            $params[] = max(0, (int)$offset);
        }
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Update agent status
     * 
     * @param int $agentId Agent ID
     * @param string $status New status (active, suspended, inactive)
     * @return bool Success status
     */
    public function updateStatus($agentId, $status)
    {
        $sql = "UPDATE agents SET status = ?, 
                       suspended_at = CASE WHEN ? = 'suspended' THEN NOW() ELSE suspended_at END,
                       activated_at = CASE WHEN ? = 'active' THEN NOW() ELSE activated_at END
                WHERE id = ?";
        
        return $this->db->query($sql, [$status, $status, $status, $agentId]);
    }
    
    /**
     * Update agent details
     * 
     * @param int $agentId Agent ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function updateAgent($agentId, $data)
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['first_name', 'last_name', 'phone', 'email', 'address', 'county', 'commission_rate'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $agentId;
        $sql = "UPDATE agents SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Record a commission for an agent
     * 
     * @param array $data Commission data
     * @return int|false Commission ID or false on failure
     */
    public function recordCommission($data)
    {
        $sql = "INSERT INTO agent_commissions (
                    agent_id, member_id, payment_id, commission_type,
                    amount, commission_rate, commission_amount, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['agent_id'],
            $data['member_id'],
            $data['payment_id'] ?? null,
            $data['commission_type'],
            $data['amount'],
            $data['commission_rate'],
            $data['commission_amount'],
            $data['status'] ?? 'pending'
        ];
        
        $stmt = $this->db->query($sql, $params);
        if ($stmt) {
            // Update agent's total commission
            $this->updateAgentCommissionTotal($data['agent_id']);
            return $this->db->getConnection()->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Get agent commissions with optional filtering
     * 
     * @param int $agentId Agent ID
     * @param array $filters Optional filters (status, date_from, date_to)
     * @return array List of commissions
     */
    public function getAgentCommissions($agentId, $filters = [])
    {
        $sql = "SELECT ac.*, m.member_number, m.package,
                       CONCAT(m.id_number) as member_name,
                       p.amount as payment_amount, p.payment_date
                FROM agent_commissions ac
                JOIN members m ON ac.member_id = m.id
                LEFT JOIN payments p ON ac.payment_id = p.id
                WHERE ac.agent_id = ?";
        
        $params = [$agentId];
        
        if (!empty($filters['status'])) {
            $sql .= " AND ac.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND ac.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND ac.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $sql .= " ORDER BY ac.created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Approve commission payment
     *
     * @param int $commissionId Commission ID
     * @param int $approvedBy User ID of approver
     * @return bool Success status
     */
    public function approveCommission($commissionId, $approvedBy)
    {
        $sql = "UPDATE agent_commissions
                SET status = 'approved', approved_by = ?, approved_at = NOW()
                WHERE id = ?";

        $result = $this->db->query($sql, [$approvedBy, $commissionId]);

        if ($result) {
            // Send notification to agent
            $this->sendCommissionNotification($commissionId, 'approved');
        }

        return $result;
    }
    
    /**
     * Mark commission as paid
     * 
     * @param int $commissionId Commission ID
     * @param string $paymentMethod Payment method used
     * @param string $paymentReference Payment reference/transaction ID
     * @return bool Success status
     */
    public function markCommissionPaid($commissionId, $paymentMethod, $paymentReference)
    {
        $sql = "UPDATE agent_commissions 
                SET status = 'paid', paid_at = NOW(), 
                    payment_method = ?, payment_reference = ?
                WHERE id = ?";
        
        if ($this->db->query($sql, [$paymentMethod, $paymentReference, $commissionId])) {
            // Get agent_id and update totals
            $commission = $this->db->query(
                "SELECT agent_id FROM agent_commissions WHERE id = ?",
                [$commissionId]
            )->fetch();
            
            if ($commission) {
                $this->updateAgentCommissionTotal($commission['agent_id']);
            }

            $this->sendCommissionNotification($commissionId, 'paid');
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update agent's total commission amounts
     * 
     * @param int $agentId Agent ID
     * @return bool Success status
     */
    private function updateAgentCommissionTotal($agentId)
    {
        $sql = "UPDATE agents 
                SET total_commission = (
                    SELECT COALESCE(SUM(commission_amount), 0) 
                    FROM agent_commissions 
                    WHERE agent_id = ? AND status = 'paid'
                )
                WHERE id = ?";
        
        return $this->db->query($sql, [$agentId, $agentId]);
    }
    
    /**
     * Get agent dashboard statistics
     * 
     * @param int $agentId Agent ID
     * @return array Dashboard statistics
     */
    public function getAgentDashboardStats($agentId)
    {
        // Total members registered
        $membersSql = "SELECT COUNT(*) as total_members,
                              COUNT(CASE WHEN status = 'active' THEN 1 END) as active_members,
                              COUNT(CASE WHEN status = 'grace_period' THEN 1 END) as grace_period_members
                       FROM members WHERE agent_id = ?";
        $membersStats = $this->db->query($membersSql, [$agentId])->fetch();
        
        // Commission statistics
        $commissionSql = "SELECT 
                             SUM(CASE WHEN status = 'pending' THEN commission_amount ELSE 0 END) as pending_commission,
                             SUM(CASE WHEN status = 'approved' THEN commission_amount ELSE 0 END) as approved_commission,
                             SUM(CASE WHEN status = 'paid' THEN commission_amount ELSE 0 END) as paid_commission,
                             COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count
                          FROM agent_commissions WHERE agent_id = ?";
        $commissionStats = $this->db->query($commissionSql, [$agentId])->fetch();
        
        // Recent registrations (last 30 days)
        $recentSql = "SELECT COUNT(*) as recent_registrations 
                      FROM members 
                      WHERE agent_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $recentStats = $this->db->query($recentSql, [$agentId])->fetch();
        
        return array_merge(
            $membersStats ?: [],
            $commissionStats ?: [],
            $recentStats ?: []
        );
    }
    
    /**
     * Get pending commissions for admin review
     * 
     * @param int $limit Optional result limit
     * @return array List of pending commissions
     */
    public function getPendingCommissions($limit = 50)
    {
        $sql = "SELECT ac.*, a.agent_number, a.first_name, a.last_name,
                       m.member_number, m.package
                FROM agent_commissions ac
                JOIN agents a ON ac.agent_id = a.id
                JOIN members m ON ac.member_id = m.id
                WHERE ac.status = 'pending'
                ORDER BY ac.created_at ASC
                LIMIT ?";
        
        return $this->db->query($sql, [$limit])->fetchAll();
    }

    /**
     * Get commissions for export (optional status filter)
     *
     * @param string $status Optional status filter
     * @return array
     */
    public function getCommissionsForExport($status = '')
    {
        $sql = "SELECT ac.*, a.agent_number, a.first_name, a.last_name,
                       m.member_number, m.package
                FROM agent_commissions ac
                JOIN agents a ON ac.agent_id = a.id
                JOIN members m ON ac.member_id = m.id
                WHERE 1=1";

        $params = [];
        if (!empty($status)) {
            $sql .= " AND ac.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY ac.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get a single commission by ID
     *
     * @param int $commissionId
     * @return array|false
     */
    public function getCommissionById($commissionId)
    {
        $sql = "SELECT * FROM agent_commissions WHERE id = ?";
        return $this->db->query($sql, [$commissionId])->fetch();
    }

    /**
     * Approve all pending commissions
     *
     * @param int $approvedBy
     * @return bool
     */
    public function approveAllPendingCommissions($approvedBy)
    {
        $sql = "UPDATE agent_commissions
                SET status = 'approved', approved_by = ?, approved_at = NOW()
                WHERE status = 'pending'";

        return $this->db->query($sql, [$approvedBy]);
    }

    /**
     * Reactivate all suspended agents
     *
     * @return bool
     */
    public function reactivateSuspendedAgents()
    {
        $sql = "UPDATE agents
                SET status = 'active', activated_at = NOW()
                WHERE status = 'suspended'";

        return $this->db->query($sql);
    }
    
    /**
     * Check if agent exists by national ID
     * 
     * @param string $nationalId National ID number
     * @return bool True if exists
     */
    public function existsByNationalId($nationalId)
    {
        $sql = "SELECT COUNT(*) as count FROM agents WHERE national_id = ?";
        $result = $this->db->query($sql, [$nationalId])->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Check if agent exists by email
     * 
     * @param string $email Email address
     * @return bool True if exists
     */
    public function existsByEmail($email)
    {
        $sql = "SELECT COUNT(*) as count FROM agents WHERE email = ?";
        $result = $this->db->query($sql, [$email])->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Update agent member count
     * 
     * @param int $agentId Agent ID
     * @return bool Success status
     */
    public function updateMemberCount($agentId)
    {
        $sql = "UPDATE agents 
                SET total_members = (
                    SELECT COUNT(*) FROM members WHERE agent_id = ?
                )
                WHERE id = ?";
        
        return $this->db->query($sql, [$agentId, $agentId]);
    }
    
    /**
     * Get total commissions across all agents
     * 
     * @return float Total commission amount
     */
    public function getTotalCommissions()
    {
        $sql = "SELECT COALESCE(SUM(commission_amount), 0) as total 
                FROM agent_commissions 
                WHERE status IN ('approved', 'paid')";
        
        $result = $this->db->query($sql)->fetch();
        return $result ? (float)$result['total'] : 0.0;
    }
    
    /**
     * Get count of active agents
     *
     * @return int Count of active agents
     */
    public function getActiveAgentsCount()
    {
        $sql = "SELECT COUNT(*) as count
                FROM agents
                WHERE status = 'active'";

        $result = $this->db->query($sql)->fetch();
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Send notification to agent about commission status change
     *
     * @param int $commissionId Commission ID
     * @param string $status New status (approved, paid)
     */
    private function sendCommissionNotification($commissionId, $status)
    {
        try {
            // Get commission details
            $commission = $this->getCommissionById($commissionId);
            if (!$commission) {
                return;
            }

            // Get agent details
            $Agent = $this->getAgentById($commission['agent_id']);
            if (!$Agent || empty($Agent['user_id'])) {
                return;
            }

            require_once __DIR__ . '/../services/InAppNotificationService.php';
            $notificationService = new InAppNotificationService();

            if ($status === 'approved') {
                $notificationService->notifyUser($Agent['user_id'], [
                    'subject' => 'Commission Approved',
                    'message' => "Your commission of KES " . number_format($commission['commission_amount'], 2) . " has been approved. It will be paid to your account soon.",
                    'action_url' => '/agent/commissions',
                    'action_text' => 'View Commissions'
                ]);
            } elseif ($status === 'paid') {
                $notificationService->notifyUser($Agent['user_id'], [
                    'subject' => 'Commission Paid',
                    'message' => "Your commission of KES " . number_format($commission['commission_amount'], 2) . " has been paid to your account.",
                    'action_url' => '/agent/payouts',
                    'action_text' => 'View Payouts'
                ]);
            }
        } catch (Exception $e) {
            error_log('Commission notification error: ' . $e->getMessage());
        }
    }
}
