<?php
/**
 * Plan Upgrade Service
 * Handles member package upgrades with prorated billing
 */

class PlanUpgradeService
{
    private $db;
    private $memberModel;
    private $paymentService;
    private $emailService;
    private $smsService;
    
    // Package monthly fees (in KES)
    const PACKAGE_FEES = [
        'individual' => 500,
        'couple' => 750,
        'family' => 1000,
        'executive' => 1500
    ];

    const CALCULATION_METHOD_PRORATED = 'prorated';
    const CALCULATION_METHOD_DIRECT = 'direct';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->memberModel = new Member();
        $this->paymentService = new PaymentService();
        $this->emailService = new EmailService();
        $this->smsService = new SmsService();
    }
    
    /**
     * Calculate prorated upgrade cost
     * 
     * @param int $memberId Member ID
     * @param string $toPackage Target package (couple, family, or executive)
     * @param string|null $customDate Custom date for calculation (for testing)
     * @return array Calculation details
     */
    public function calculateUpgradeCost($memberId, $toPackage = 'couple', $customDate = null)
    {
        // Get member with user information
        $query = "SELECT m.*, u.first_name, u.last_name, u.email, u.phone 
                  FROM members m 
                  JOIN users u ON m.user_id = u.id 
                  WHERE m.id = ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute([$memberId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$member) {
            throw new Exception('Member not found');
        }
        
        $fromPackage = $this->memberModel->normalizePackageTier($member['package'] ?? 'individual');
        $toPackage = $this->memberModel->normalizePackageTier($toPackage);
        
        if ($fromPackage === $toPackage) {
            throw new Exception('Member is already on ' . $toPackage . ' package');
        }
        
        if ($fromPackage === 'executive') {
            throw new Exception('Cannot upgrade from executive package - already at highest tier');
        }
        
        // Validate upgrade path (must be upgrading to a higher tier)
        $packageHierarchy = ['individual' => 1, 'couple' => 2, 'family' => 3, 'executive' => 4];
        if (!isset($packageHierarchy[$fromPackage]) || !isset($packageHierarchy[$toPackage])) {
            throw new Exception('Invalid package specified');
        }
        if ($packageHierarchy[$toPackage] <= $packageHierarchy[$fromPackage]) {
            throw new Exception('Can only upgrade to a higher tier package');
        }
        
        // Get current and new monthly fees
        $currentFee = self::PACKAGE_FEES[$fromPackage];
        $newFee = self::PACKAGE_FEES[$toPackage];
        
        // Calculate days remaining in current month
        $today = $customDate ? new DateTime($customDate) : new DateTime();
        $lastDayOfMonth = new DateTime($today->format('Y-m-t'));
        $daysRemaining = $today->diff($lastDayOfMonth)->days + 1; // Include today
        $totalDaysInMonth = $today->format('t');
        
        $monthlyDifference = max(0, $newFee - $currentFee);
        $calculationMethod = $this->resolveUpgradeCalculationMethod($fromPackage, $toPackage);

        if ($calculationMethod === self::CALCULATION_METHOD_DIRECT) {
            $amountDueToday = round($monthlyDifference, 2);
        } else {
            // Prorated = (New Fee - Current Fee) × (Days Remaining / Total Days)
            $amountDueToday = $monthlyDifference * ($daysRemaining / $totalDaysInMonth);
            $amountDueToday = round($amountDueToday, 2);
        }
        
        // Effective date is immediate
        $effectiveDate = $today->format('Y-m-d');
        
        return [
            'member_id' => $memberId,
            'member_number' => $member['member_number'],
            'member_name' => $member['first_name'] . ' ' . $member['last_name'],
            'from_package' => $fromPackage,
            'to_package' => $toPackage,
            'current_monthly_fee' => $currentFee,
            'new_monthly_fee' => $newFee,
            'monthly_difference' => $monthlyDifference,
            'days_remaining' => $daysRemaining,
            'total_days_in_month' => $totalDaysInMonth,
            'prorated_amount' => $amountDueToday,
            'amount_due_today' => $amountDueToday,
            'calculation_method' => $calculationMethod,
            'effective_date' => $effectiveDate,
            'next_full_payment_date' => date('Y-m-01', strtotime('first day of next month'))
        ];
    }

    /**
     * Determine upgrade calculation method.
     * Couple -> Family/Executive upgrades are billed as direct upgrades (no day proration).
     *
     * @param string $fromPackage
     * @param string $toPackage
     * @return string
     */
    private function resolveUpgradeCalculationMethod($fromPackage, $toPackage)
    {
        if ($fromPackage === 'couple' && in_array($toPackage, ['family', 'executive'], true)) {
            return self::CALCULATION_METHOD_DIRECT;
        }

        return self::CALCULATION_METHOD_PRORATED;
    }
    
    /**
     * Create upgrade request
     * 
     * @param int $memberId Member ID
     * @param string $toPackage Target package
     * @return int Upgrade request ID
     */
    public function createUpgradeRequest($memberId, $toPackage = 'couple')
    {
        $calculation = $this->calculateUpgradeCost($memberId, $toPackage);
        
        $query = "INSERT INTO plan_upgrade_requests (
            member_id, from_package, to_package, 
            current_monthly_fee, new_monthly_fee, prorated_amount,
            days_remaining, status, effective_date
        ) VALUES (
            :member_id, :from_package, :to_package,
            :current_monthly_fee, :new_monthly_fee, :prorated_amount,
            :days_remaining, 'pending', :effective_date
        )";
        
        $params = [
            'member_id' => $calculation['member_id'],
            'from_package' => $calculation['from_package'],
            'to_package' => $calculation['to_package'],
            'current_monthly_fee' => $calculation['current_monthly_fee'],
            'new_monthly_fee' => $calculation['new_monthly_fee'],
            'prorated_amount' => $calculation['prorated_amount'],
            'days_remaining' => $calculation['days_remaining'],
            'effective_date' => $calculation['effective_date']
        ];
        
        $this->db->execute($query, $params);
        
        return $this->db->getConnection()->lastInsertId();
    }
    
    /**
     * Initiate M-Pesa payment for upgrade
     * 
     * @param int $upgradeRequestId Upgrade request ID
     * @param string $phoneNumber Phone number for M-Pesa
     * @return array Payment initiation response
     */
    public function initiateUpgradePayment($upgradeRequestId, $phoneNumber)
    {
        $request = $this->getUpgradeRequest($upgradeRequestId);
        
        if (!$request) {
            throw new Exception('Upgrade request not found');
        }
        
        if ($request['status'] !== 'pending') {
            throw new Exception('Upgrade request is not in pending status');
        }
        
        // Initiate M-Pesa STK Push
        $response = $this->paymentService->initiateSTKPush(
            $phoneNumber,
            $request['prorated_amount'],
            'UPG' . $upgradeRequestId,
            'Package Upgrade: ' . ucfirst($request['from_package']) . ' to ' . ucfirst($request['to_package'])
        );
        
        if ($response && isset($response['CheckoutRequestID'])) {
            // Update upgrade request with payment details
            $this->updateUpgradeRequest($upgradeRequestId, [
                'status' => 'payment_initiated',
                'payment_method' => 'mpesa',
                'mpesa_checkout_id' => $response['CheckoutRequestID']
            ]);
            
            return [
                'success' => true,
                'message' => 'Payment initiated. Please check your phone for M-Pesa prompt.',
                'checkout_request_id' => $response['CheckoutRequestID'],
                'amount' => $request['prorated_amount']
            ];
        }
        
        throw new Exception('Failed to initiate M-Pesa payment');
    }
    
    /**
     * Process successful payment and complete upgrade
     * 
     * @param int $upgradeRequestId Upgrade request ID
     * @param array $paymentDetails Payment details (receipt, date, etc)
     * @return array Success status and details
     */
    public function completeUpgrade($upgradeRequestId, $paymentDetails = [])
    {
        $request = $this->getUpgradeRequest($upgradeRequestId);
        
        if (!$request) {
            throw new Exception('Upgrade request not found');
        }
        
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Update member package
            // Recalculate monthly contribution centrally (considers dependents)
            $member = $this->memberModel->getMemberById($request['member_id']);
            $dependents = $this->memberModel->getMemberDependents($request['member_id']);
            // Ensure member array includes the new target package key for calculation
            $memberForCalc = $member;
            $memberForCalc['package'] = $request['to_package'];
            $newMonthly = $this->memberModel->calculateMonthlyContribution($memberForCalc, $dependents);

            $updateMember = "UPDATE members SET 
                package = :package,
                monthly_contribution = :monthly_contribution,
                last_upgrade_date = NOW(),
                upgrade_count = upgrade_count + 1
                WHERE id = :member_id";

            $this->db->execute($updateMember, [
                'package' => $request['to_package'],
                'monthly_contribution' => $newMonthly,
                'member_id' => $request['member_id']
            ]);
            
            // Update upgrade request status
            $updateRequest = "UPDATE plan_upgrade_requests SET 
                status = 'completed',
                payment_date = NOW(),
                completed_at = NOW(),
                mpesa_receipt_number = :receipt_number
                WHERE id = :id";
            
            $this->db->execute($updateRequest, [
                'receipt_number' => $paymentDetails['receipt_number'] ?? null,
                'id' => $upgradeRequestId
            ]);
            
            // Create upgrade history record
            $insertHistory = "INSERT INTO plan_upgrade_history (
                member_id, upgrade_request_id, from_package, to_package,
                amount_paid, payment_method, payment_reference, effective_date
            ) VALUES (
                :member_id, :upgrade_request_id, :from_package, :to_package,
                :amount_paid, :payment_method, :payment_reference, :effective_date
            )";
            
            $this->db->execute($insertHistory, [
                'member_id' => $request['member_id'],
                'upgrade_request_id' => $upgradeRequestId,
                'from_package' => $request['from_package'],
                'to_package' => $request['to_package'],
                'amount_paid' => $request['prorated_amount'],
                'payment_method' => $paymentDetails['payment_method'] ?? 'mpesa',
                'payment_reference' => $paymentDetails['receipt_number'] ?? null,
                'effective_date' => $request['effective_date']
            ]);
            
            $this->db->getConnection()->commit();
            
            // Send notifications
            $this->sendUpgradeNotifications($request);
            
            return [
                'success' => true,
                'message' => 'Upgrade completed successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    /**
     * Cancel upgrade request
     * 
     * @param int $upgradeRequestId Upgrade request ID
     * @param string $reason Cancellation reason
     * @return bool Success status
     */
    public function cancelUpgrade($upgradeRequestId, $reason = 'Cancelled by member')
    {
        $query = "UPDATE plan_upgrade_requests SET 
            status = 'cancelled',
            cancelled_at = NOW(),
            cancellation_reason = :reason
            WHERE id = :id AND status IN ('pending', 'payment_initiated')";
        
        $this->db->execute($query, [
            'id' => $upgradeRequestId,
            'reason' => $reason
        ]);
        
        return true;
    }

    /**
     * Approve an upgrade request (admin action)
     * Sets the request to payment_initiated so payment can be processed
     * @param int $upgradeRequestId
     * @param int|null $approvedBy
     * @return bool
     */
    public function approveUpgrade($upgradeRequestId, $approvedBy = null)
    {
        $data = ['status' => 'payment_initiated'];
        if ($approvedBy !== null) $data['processed_by'] = $approvedBy;
        $this->updateUpgradeRequest($upgradeRequestId, $data);

        return true;
    }

    /**
     * Reject an upgrade request (admin action)
     * Marks the request as cancelled with a reason
     * @param int $upgradeRequestId
     * @param string $reason
     * @param int|null $rejectedBy
     * @return bool
     */
    public function rejectUpgrade($upgradeRequestId, $reason = 'Rejected by admin', $rejectedBy = null)
    {
        $data = [
            'status' => 'cancelled',
            'cancellation_reason' => $reason
        ];
        if ($rejectedBy !== null) $data['processed_by'] = $rejectedBy;
        $this->updateUpgradeRequest($upgradeRequestId, $data);

        return true;
    }
    
    /**
     * Get upgrade request details
     * 
     * @param int $upgradeRequestId Upgrade request ID
     * @return array|null Upgrade request details
     */
    public function getUpgradeRequest($upgradeRequestId)
    {
        $query = "SELECT pur.*, 
                  m.member_number, m.user_id,
                  u.first_name, u.last_name, u.email, u.phone
                  FROM plan_upgrade_requests pur
                  JOIN members m ON pur.member_id = m.id
                  JOIN users u ON m.user_id = u.id
                  WHERE pur.id = :id";
        
        return $this->db->fetchAll($query, ['id' => $upgradeRequestId])[0] ?? null;
    }
    
    /**
     * Get upgrade request status
     * 
     * @param int $upgradeRequestId Upgrade request ID
     * @return array Status information
     */
    public function getUpgradeRequestStatus($upgradeRequestId)
    {
        $request = $this->getUpgradeRequest($upgradeRequestId);
        
        if (!$request) {
            return [
                'success' => false,
                'error' => 'Upgrade request not found'
            ];
        }
        
        return [
            'success' => true,
            'status' => $request['status'],
            'upgrade_request_id' => $request['id'],
            'from_package' => $request['from_package'],
            'to_package' => $request['to_package'],
            'prorated_amount' => $request['prorated_amount'],
            'requested_at' => $request['requested_at'],
            'completed_at' => $request['completed_at'] ?? null
        ];
    }
    
    /**
     * Get member's upgrade history
     * 
     * @param int $memberId Member ID
     * @return array Upgrade history
     */
    public function getMemberUpgradeHistory($memberId)
    {
        try {
            $query = "SELECT * FROM plan_upgrade_history 
                      WHERE member_id = :member_id 
                      ORDER BY upgraded_at DESC";
            
            return $this->db->fetchAll($query, ['member_id' => $memberId]);
        } catch (Exception $e) {
            error_log("Get upgrade history error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get member's pending upgrade requests
     * 
     * @param int $memberId Member ID
     * @return array Pending requests
     */
    public function getMemberPendingUpgrades($memberId)
    {
        try {
            $query = "SELECT * FROM plan_upgrade_requests 
                      WHERE member_id = :member_id 
                      AND status IN ('pending', 'payment_initiated')
                      ORDER BY requested_at DESC";
            
            return $this->db->fetchAll($query, ['member_id' => $memberId]);
        } catch (Exception $e) {
            error_log("Get pending upgrades error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all pending upgrades (admin view)
     * 
     * @return array Pending upgrades
     */
    public function getAllPendingUpgrades()
    {
        return $this->db->fetchAll("SELECT * FROM vw_pending_upgrades");
    }
    
    /**
     * Get upgrade statistics
     * 
     * @return array Statistics
     */
    public function getUpgradeStatistics()
    {
        $result = $this->db->fetchAll("SELECT * FROM vw_upgrade_statistics");
        return $result[0] ?? [];
    }
    
    /**
     * Update upgrade request
     * 
     * @param int $upgradeRequestId Upgrade request ID
     * @param array $data Data to update
     * @return bool Success status
     */
    private function updateUpgradeRequest($upgradeRequestId, $data)
    {
        $sets = [];
        $params = ['id' => $upgradeRequestId];
        
        foreach ($data as $key => $value) {
            $sets[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        $query = "UPDATE plan_upgrade_requests SET " . implode(', ', $sets) . " WHERE id = :id";
        $this->db->execute($query, $params);
        
        return true;
    }
    
    /**
     * Send upgrade notifications
     * 
     * @param array $request Upgrade request details
     */
    private function sendUpgradeNotifications($request)
    {
        try {
            // Send email notification
            $siteName = defined('SITE_NAME') ? SITE_NAME : 'Shena Welfare';
            $subject = 'Package Upgrade Successful - ' . $siteName;
            $message = "
                <h2>Package Upgrade Successful!</h2>
                <p>Dear {$request['first_name']},</p>
                <p>Your package has been successfully upgraded:</p>
                <ul>
                    <li><strong>From:</strong> " . ucfirst($request['from_package']) . " (KES " . number_format($request['current_monthly_fee'], 2) . "/month)</li>
                    <li><strong>To:</strong> " . ucfirst($request['to_package']) . " (KES " . number_format($request['new_monthly_fee'], 2) . "/month)</li>
                    <li><strong>Amount Paid:</strong> KES " . number_format($request['prorated_amount'], 2) . " (prorated)</li>
                    <li><strong>Effective Date:</strong> " . date('d M Y', strtotime($request['effective_date'])) . "</li>
                </ul>
                <p>Your next full monthly payment of KES " . number_format($request['new_monthly_fee'], 2) . " will be due on the 1st of next month.</p>
                <p>Thank you for upgrading your membership!</p>
            ";
            
            $this->emailService->sendEmail($request['email'], $subject, $message);
            
            // Send SMS notification
            $smsMessage = "Package upgrade successful! You are now on " . ucfirst($request['to_package']) . 
                         " plan (KES " . number_format($request['new_monthly_fee'], 2) . "/month). " .
                         "Next payment: 1st of next month. Thank you!";
            
            $this->smsService->sendSms($request['phone'], $smsMessage);
            
        } catch (Exception $e) {
            // Log error but don't fail the upgrade
            error_log('Upgrade notification error: ' . $e->getMessage());
        }
    }
}
