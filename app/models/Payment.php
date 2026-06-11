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
        if ($this->hasPaidRegistrationFee($memberId)) {
            $this->cancelStaleRegistrationPaymentAttempts((int)$memberId);
        }

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
            'payment_date' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($transactionId) {
            // BUGFIX: Store to mpesa_receipt_number for proper STK callback handling
            // Also keep transaction_id for backward compatibility
            $data['mpesa_receipt_number'] = $transactionId;
            $data['transaction_id'] = $transactionId;
        }

        $this->update($paymentId, $data);

        // Apply membership-side effects for monthly contributions (coverage, status, grace period)
        $payment = $this->find($paymentId);
        if (!$payment || empty($payment['member_id'])) {
            return true;
        }

        if (isset($payment['payment_type']) && $payment['payment_type'] === 'monthly') {
            $memberModel = new Member();
            $memberModel->applySuccessfulMonthlyPayment(
                $payment['member_id'],
                $payment['payment_date'] ?? $payment['created_at'] ?? date('Y-m-d H:i:s')
            );
        }

        if (isset($payment['payment_type']) && $payment['payment_type'] === 'registration') {
            $this->activateMemberAfterRegistrationPayment((int)$payment['member_id']);
        }

        if (isset($payment['payment_type']) && $payment['payment_type'] === 'reactivation') {
            $memberModel = new Member();
            $memberModel->reactivateMember((int)$payment['member_id']);
        }

        return true;
    }

    /**
     * Check if registration fee has been paid
     *
     * @param int $memberId
     * @return bool
     */
    public function hasPaidRegistrationFee($memberId)
    {
        $registrationFeeRequired = defined('REGISTRATION_FEE') ? REGISTRATION_FEE : 200;

        $sql = "SELECT COALESCE(SUM(amount), 0) as total_paid
                FROM {$this->table}
                WHERE member_id = :member_id
                AND payment_type = 'registration'
                AND status = 'completed'";

        $result = $this->db->fetch($sql, ['member_id' => $memberId]);

        return ($result['total_paid'] ?? 0) >= $registrationFeeRequired;
    }

    public function hasPaidReactivationFee($memberId)
    {
        $reactivationFeeRequired = defined('REACTIVATION_FEE') ? REACTIVATION_FEE : 100;

        $sql = "SELECT COALESCE(SUM(amount), 0) as total_paid
                FROM {$this->table}
                WHERE member_id = :member_id
                AND payment_type = 'reactivation'
                AND status = 'completed'";

        $result = $this->db->fetch($sql, ['member_id' => $memberId]);

        return ($result['total_paid'] ?? 0) >= $reactivationFeeRequired;
    }

    public function activateMemberAfterRegistrationPayment($memberId)
    {
        if (!$this->hasPaidRegistrationFee($memberId)) {
            return false;
        }

        $memberModel = new Member();
        $member = $memberModel->find($memberId);
        if (!$member) {
            return false;
        }

        $wasActive = (($member['status'] ?? '') === 'active');

        if (($member['status'] ?? '') !== 'active') {
            $memberModel->update($memberId, [
                'status' => 'active',
                'coverage_ends' => date('Y-m-d', strtotime('+1 year'))
            ]);
        }

        if (!empty($member['user_id'])) {
            $userModel = new User();
            $userModel->update($member['user_id'], ['status' => 'active']);
        }

        $this->cancelStaleRegistrationPaymentAttempts($memberId);

        if (!$wasActive) {
            $this->sendRegistrationWelcomeSms($memberId);
        }

        return true;
    }

    private function cancelStaleRegistrationPaymentAttempts(int $memberId): void
    {
        $sql = "UPDATE {$this->table}
                SET status = 'cancelled',
                    notes = CASE
                        WHEN notes IS NULL OR notes = '' THEN 'Cancelled after registration fee was completed'
                        ELSE CONCAT(notes, ' | Cancelled after registration fee was completed')
                    END,
                    updated_at = NOW()
                WHERE member_id = :member_id
                  AND payment_type = 'registration'
                  AND status = 'pending'";

        $this->db->execute($sql, ['member_id' => $memberId]);
    }

    private function sendRegistrationWelcomeSms(int $memberId): void
    {
        try {
            $memberModel = new Member();
            $member = $memberModel->getMemberWithUser($memberId);

            if (!$member || empty($member['phone'])) {
                return;
            }

            $firstName = $member['first_name'] ?? 'Member';
            $memberNo = $member['member_number'] ?? '';
            $nationalId = $member['id_number'] ?? $memberNo;
            $contribution = number_format((float)($member['monthly_contribution'] ?? 0), 0);

            $smsMsg = "Hi {$firstName}! Welcome to SHENA. Your monthly contribution is KES {$contribution} to be paid by the 7th of every month via Paybill 4163987, Acct: {$nationalId}. {$memberNo} is your member number.";

            $smsService = new SmsService();
            $smsService->sendSms($member['phone'], $smsMsg);
        } catch (Exception $smsEx) {
            error_log('Registration welcome SMS error for member ' . $memberId . ': ' . $smsEx->getMessage());
        }
    }

    /**
     * Find payment by M-Pesa receipt number
     *
     * @param string $receiptNumber
     * @return array|null
     */
    public function findByReceiptNumber($receiptNumber)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE mpesa_receipt_number = :receipt OR transaction_id = :receipt_txn
                LIMIT 1";

        return $this->db->fetch($sql, [
            'receipt' => $receiptNumber,
            'receipt_txn' => $receiptNumber
        ]);
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

    public function getAllPaymentsWithDetails($conditions = [], $limit = null, $offset = 0)
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

        [$where_clauses, $params] = $this->buildPaymentFilterClause($conditions);

        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $sql .= " ORDER BY p.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params['limit'] = max(1, min((int)$limit, 200));
            $params['offset'] = max(0, (int)$offset);
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function getAllPaymentsWithDetailsCount($conditions = [])
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} p
                JOIN members m ON p.member_id = m.id
                JOIN users u ON m.user_id = u.id";

        [$where_clauses, $params] = $this->buildPaymentFilterClause($conditions);

        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $result = $this->db->fetch($sql, $params);
        return (int)($result['total'] ?? 0);
    }

    private function buildPaymentFilterClause($conditions = [])
    {
        $params = [];
        $where_clauses = [];

        foreach ((array)$conditions as $field => $value) {
            if ($field === 'member_id' && !empty($value)) {
                $where_clauses[] = "p.member_id = :member_id";
                $params['member_id'] = (int)$value;
            }

            if ($field === 'status' && $value !== '' && $value !== 'all') {
                $where_clauses[] = "p.status = :status";
                $params['status'] = $value;
            }

            if (($field === 'start_date' || $field === 'date_from') && $value !== '') {
                $where_clauses[] = "p.created_at >= :date_from";
                $params['date_from'] = $value . ' 00:00:00';
            }

            if (($field === 'end_date' || $field === 'date_to') && $value !== '') {
                $where_clauses[] = "p.created_at <= :date_to";
                $params['date_to'] = $value . ' 23:59:59';
            }

            if ($field === 'payment_method' && $value !== '' && $value !== 'all') {
                $where_clauses[] = "p.payment_method = :payment_method";
                $params['payment_method'] = $value;
            }

            if ($field === 'payment_type' && $value !== '' && $value !== 'all') {
                $where_clauses[] = "p.payment_type = :payment_type";
                $params['payment_type'] = $value;
            }

            if ($field === 'reconciliation_status' && $value !== '' && $value !== 'all') {
                $where_clauses[] = "p.reconciliation_status = :reconciliation_status";
                $params['reconciliation_status'] = $value;
            }

            if ($field === 'search' && trim((string)$value) !== '') {
                $searchTerm = '%' . trim((string)$value) . '%';
                $where_clauses[] = "(p.transaction_id LIKE :payment_search_transaction
                    OR p.mpesa_receipt_number LIKE :payment_search_receipt
                    OR p.transaction_reference LIKE :payment_search_reference
                    OR p.paybill_account LIKE :payment_search_account
                    OR p.sender_name LIKE :payment_search_sender
                    OR p.sender_phone LIKE :payment_search_phone
                    OR m.member_number LIKE :payment_search_member_number
                    OR m.id_number LIKE :payment_search_id_number
                    OR u.first_name LIKE :payment_search_first_name
                    OR u.last_name LIKE :payment_search_last_name
                    OR u.email LIKE :payment_search_email
                    OR u.phone LIKE :payment_search_user_phone)";
                $params['payment_search_transaction'] = $searchTerm;
                $params['payment_search_receipt'] = $searchTerm;
                $params['payment_search_reference'] = $searchTerm;
                $params['payment_search_account'] = $searchTerm;
                $params['payment_search_sender'] = $searchTerm;
                $params['payment_search_phone'] = $searchTerm;
                $params['payment_search_member_number'] = $searchTerm;
                $params['payment_search_id_number'] = $searchTerm;
                $params['payment_search_first_name'] = $searchTerm;
                $params['payment_search_last_name'] = $searchTerm;
                $params['payment_search_email'] = $searchTerm;
                $params['payment_search_user_phone'] = $searchTerm;
            }
        }

        return [$where_clauses, $params];
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
