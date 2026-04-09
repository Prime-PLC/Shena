<?php
/**
 * Payment Reconciliation Service
 * Handles automatic and manual reconciliation of M-Pesa Paybill payments
 * Per Phase 2 requirements
 */

class PaymentReconciliationService
{
    private $db;
    private $paymentModel;
    private $memberModel;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->paymentModel = new Payment();
        $this->memberModel = new Member();
    }
    
    /**
     * Process M-Pesa C2B callback from Paybill
     * @param array $callbackData Raw callback data from M-Pesa
     * @return array Result with success status and payment_id if matched
     */
    public function processC2BCallback($callbackData)
    {
        try {
            // Extract callback data
            $transId = $callbackData['TransID'] ?? '';
            $transTime = $callbackData['TransTime'] ?? '';
            $transAmount = $callbackData['TransAmount'] ?? 0;
            $businessShortCode = $callbackData['BusinessShortCode'] ?? '';
            $billRefNumber = $callbackData['BillRefNumber'] ?? ''; // Member's ID number or member number
            $msisdn = $callbackData['MSISDN'] ?? '';
            $firstName = $callbackData['FirstName'] ?? '';
            $middleName = $callbackData['MiddleName'] ?? '';
            $lastName = $callbackData['LastName'] ?? '';
            
            // Check if callback already processed
            $existing = $this->db->fetch(
                "SELECT id FROM mpesa_c2b_callbacks WHERE trans_id = :trans_id",
                ['trans_id' => $transId]
            );
            
            if ($existing) {
                return [
                    'success' => false,
                    'message' => 'Callback already processed',
                    'duplicate' => true
                ];
            }
            
            // Store raw callback
            $callbackId = $this->storeCallback($callbackData);
            
            // Attempt auto-reconciliation
            $reconciliationResult = $this->autoReconcilePayment([
                'trans_id' => $transId,
                'amount' => $transAmount,
                'transaction_date' => $this->parseTransTime($transTime),
                'sender_phone' => $this->formatPhoneNumber($msisdn),
                'sender_name' => trim("$firstName $middleName $lastName"),
                'bill_ref_number' => $billRefNumber,
                'callback_id' => $callbackId
            ]);
            
            return $reconciliationResult;
            
        } catch (Exception $e) {
            error_log('C2B Callback Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process callback: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Store raw C2B callback in database
     */
    private function storeCallback($callbackData)
    {
        $query = "INSERT INTO mpesa_c2b_callbacks (
            transaction_type, trans_id, trans_time, trans_amount,
            business_short_code, bill_ref_number, invoice_number,
            org_account_balance, third_party_trans_id, msisdn,
            first_name, middle_name, last_name, raw_callback
        ) VALUES (
            :transaction_type, :trans_id, :trans_time, :trans_amount,
            :business_short_code, :bill_ref_number, :invoice_number,
            :org_account_balance, :third_party_trans_id, :msisdn,
            :first_name, :middle_name, :last_name, :raw_callback
        )";
        
        $params = [
            'transaction_type' => $callbackData['TransactionType'] ?? 'Pay Bill',
            'trans_id' => $callbackData['TransID'] ?? '',
            'trans_time' => $callbackData['TransTime'] ?? '',
            'trans_amount' => $callbackData['TransAmount'] ?? 0,
            'business_short_code' => $callbackData['BusinessShortCode'] ?? '',
            'bill_ref_number' => $callbackData['BillRefNumber'] ?? '',
            'invoice_number' => $callbackData['InvoiceNumber'] ?? null,
            'org_account_balance' => $callbackData['OrgAccountBalance'] ?? null,
            'third_party_trans_id' => $callbackData['ThirdPartyTransID'] ?? null,
            'msisdn' => $callbackData['MSISDN'] ?? '',
            'first_name' => $callbackData['FirstName'] ?? '',
            'middle_name' => $callbackData['MiddleName'] ?? '',
            'last_name' => $callbackData['LastName'] ?? '',
            'raw_callback' => json_encode($callbackData)
        ];
        
        $this->db->execute($query, $params);
        return $this->db->getConnection()->lastInsertId();
    }
    
    /**
     * Automatically reconcile payment with member account
     * @param array $paymentData Payment details from callback
     * @return array Result with matched member info
     */
    public function autoReconcilePayment($paymentData)
    {
        $billRefNumber = $paymentData['bill_ref_number'] ?? '';
        $senderPhone = $paymentData['sender_phone'] ?? '';
        
        // Strategy 1: Match by ID number (highest confidence)
        $member = $this->memberModel->findByIdNumber($billRefNumber);
        $matchMethod = null;
        $confidence = 0;
        
        if ($member) {
            $matchMethod = 'auto_id_number';
            $confidence = 95;
        } else {
            // Strategy 2: Match by member number
            $member = $this->memberModel->findByMemberNumber($billRefNumber);
            if ($member) {
                $matchMethod = 'auto_member_number';
                $confidence = 90;
            } else {
                // Strategy 3: Match by phone number (lower confidence)
                $member = $this->memberModel->findByPhone($senderPhone);
                if ($member) {
                    $matchMethod = 'auto_phone';
                    $confidence = 70;
                }
            }
        }
        
        if ($member && $confidence >= 70) {
            // Determine payment type from member's current status
            $paymentType = 'monthly';
            if ($member['status'] === 'inactive') {
                $paymentType = 'registration';
            } elseif ($member['status'] === 'defaulted') {
                $paymentType = 'reactivation';
            }

            // Create payment record with correct type
            $paymentId = $this->createReconciledPayment($member['id'], $paymentData, true, $paymentType);

            // Apply membership side-effects (coverage extension, grace period clearance)
            if ($paymentType === 'monthly') {
                $this->memberModel->applySuccessfulMonthlyPayment(
                    $member['id'],
                    $paymentData['transaction_date'] ?? date('Y-m-d H:i:s')
                );
            } elseif ($paymentType === 'reactivation') {
                $this->memberModel->reactivateMember($member['id']);
            } elseif ($paymentType === 'registration') {
                $totalPaid = $this->db->fetchColumn(
                    "SELECT COALESCE(SUM(amount), 0) FROM payments
                     WHERE member_id = :mid AND payment_type = 'registration' AND status = 'completed'",
                    ['mid' => $member['id']]
                );
                if ($totalPaid >= REGISTRATION_FEE) {
                    require_once ROOT_PATH . '/app/models/User.php';
                    $this->memberModel->update($member['id'], [
                        'status' => 'active',
                        'coverage_ends' => date('Y-m-d', strtotime('+1 year'))
                    ]);
                    $userModel = new User();
                    $userModel->update($member['user_id'], ['status' => 'active']);
                }
            }

            // Send payment confirmation SMS
            try {
                require_once ROOT_PATH . '/app/services/SmsService.php';
                $memberData = $this->memberModel->getMemberWithUser($member['id']);
                if ($memberData && !empty($memberData['phone'])) {
                    $smsService = new SmsService();
                    $smsService->sendPaymentConfirmationSms($memberData['phone'], [
                        'amount'         => $paymentData['amount'],
                        'transaction_id' => $paymentData['trans_id'],
                    ]);
                }
            } catch (Exception $notifEx) {
                error_log('C2B auto-match SMS error: ' . $notifEx->getMessage());
            }

            // Log reconciliation
            $this->logReconciliation($paymentId, [
                'action' => 'matched',
                'previous_status' => 'pending',
                'new_status' => 'matched',
                'matched_member_id' => $member['id'],
                'match_method' => $matchMethod,
                'confidence_score' => $confidence,
                'notes' => "Auto-matched via {$matchMethod}"
            ]);

            // Mark callback as processed
            if (isset($paymentData['callback_id'])) {
                $this->markCallbackProcessed($paymentData['callback_id'], $paymentId);
            }

            return [
                'success'      => true,
                'matched'      => true,
                'payment_id'   => $paymentId,
                'member_id'    => $member['id'],
                'member_name'  => $member['first_name'] . ' ' . $member['last_name'],
                'match_method' => $matchMethod,
                'confidence'   => $confidence,
                'payment_type' => $paymentType,
            ];
        } else {
            // No match found - create unmatched payment
            $paymentId = $this->createUnmatchedPayment($paymentData);
            
            return [
                'success' => true,
                'matched' => false,
                'payment_id' => $paymentId,
                'message' => 'Payment recorded as unmatched - requires manual reconciliation'
            ];
        }
    }
    
    /**
     * Create reconciled payment record
     */
    private function createReconciledPayment($memberId, $paymentData, $autoMatched = false, $paymentType = 'monthly')
    {
        $query = "INSERT INTO payments (
            member_id, amount, payment_type, payment_method, payment_date, status,
            reconciliation_status, mpesa_receipt_number, transaction_date,
            sender_phone, sender_name, paybill_account, auto_matched,
            reconciled_at, created_at
        ) VALUES (
            :member_id, :amount, :payment_type, :payment_method, :payment_date, :status,
            :reconciliation_status, :mpesa_receipt_number, :transaction_date,
            :sender_phone, :sender_name, :paybill_account, :auto_matched,
            :reconciled_at, NOW()
        )";

        $params = [
            'member_id'            => $memberId,
            'amount'               => $paymentData['amount'],
            'payment_type'         => $paymentType,
            'payment_method'       => 'mpesa',
            'payment_date'         => $paymentData['transaction_date'],
            'status'               => 'completed',
            'reconciliation_status'=> 'matched',
            'mpesa_receipt_number' => $paymentData['trans_id'],
            'transaction_date'     => $paymentData['transaction_date'],
            'sender_phone'         => $paymentData['sender_phone'],
            'sender_name'          => $paymentData['sender_name'] ?? '',
            'paybill_account'      => $paymentData['bill_ref_number'] ?? '',
            'auto_matched'         => $autoMatched ? 1 : 0,
            'reconciled_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->execute($query, $params);
        return $this->db->getConnection()->lastInsertId();
    }
    
    /**
     * Create unmatched payment record for manual reconciliation
     */
    private function createUnmatchedPayment($paymentData)
    {
        // For unmatched payments, member_id is NULL - admin will manually assign later
        $query = "INSERT INTO payments (
            member_id, amount, payment_method, payment_date, status,
            reconciliation_status, mpesa_receipt_number, transaction_date,
            sender_phone, sender_name, paybill_account, created_at
        ) VALUES (
            NULL, :amount, :payment_method, :payment_date, :status,
            :reconciliation_status, :mpesa_receipt_number, :transaction_date,
            :sender_phone, :sender_name, :paybill_account, NOW()
        )";
        
        $params = [
            'amount' => $paymentData['amount'],
            'payment_method' => 'mpesa',
            'payment_date' => $paymentData['transaction_date'],
            'status' => 'pending',
            'reconciliation_status' => 'unmatched',
            'mpesa_receipt_number' => $paymentData['trans_id'],
            'transaction_date' => $paymentData['transaction_date'],
            'sender_phone' => $paymentData['sender_phone'],
            'sender_name' => $paymentData['sender_name'] ?? '',
            'paybill_account' => $paymentData['bill_ref_number'] ?? ''
        ];
        
        $this->db->execute($query, $params);
        return $this->db->getConnection()->lastInsertId();
    }
    
    /**
     * Manually reconcile unmatched payment with member
     * @param int $paymentId Payment ID to reconcile
     * @param int $memberId Member ID to match with
     * @param int $userId Admin user performing reconciliation
     * @param string $notes Reconciliation notes
     * @return bool Success status
     */
    public function manualReconciliation($paymentId, $memberId, $userId, $notes = '')
    {
        try {
            // Assign the member if not already set
            $this->db->execute(
                "UPDATE payments SET
                    member_id            = :member_id,
                    reconciliation_status = 'manual',
                    reconciled_at        = NOW(),
                    reconciled_by        = :reconciled_by,
                    reconciliation_notes = :notes
                 WHERE id = :payment_id",
                [
                    'member_id'    => $memberId,
                    'reconciled_by'=> $userId,
                    'notes'        => $notes,
                    'payment_id'   => $paymentId,
                ]
            );

            // confirmPayment handles status=completed + applySuccessfulMonthlyPayment
            $this->paymentModel->confirmPayment($paymentId);

            // Log reconciliation
            $this->logReconciliation($paymentId, [
                'action'           => 'manual_match',
                'previous_status'  => 'unmatched',
                'new_status'       => 'manual',
                'matched_member_id'=> $memberId,
                'match_method'     => 'manual',
                'confidence_score' => 100,
                'reconciled_by'    => $userId,
                'notes'            => $notes,
            ]);

            return true;
        } catch (Exception $e) {
            error_log('Manual reconciliation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify a Paybill payment by M-Pesa receipt number and post it to a member
     * @param string $transId M-Pesa receipt number
     * @param int $memberId Member ID to post payment to
     * @param int $userId Admin user ID performing verification
     * @param string $notes Verification notes
     * @param string $paymentType Payment type (monthly/registration/etc)
     * @return array Result details
     */
    public function verifyPaybillReceipt($transId, $memberId, $userId, $notes = '', $paymentType = 'monthly')
    {
        try {
            $transId = trim($transId);
            if (empty($transId)) {
                return ['success' => false, 'message' => 'Receipt number is required'];
            }

            $callback = $this->db->fetch(
                "SELECT * FROM mpesa_c2b_callbacks WHERE trans_id = :trans_id LIMIT 1",
                ['trans_id' => $transId]
            );

            if (!$callback) {
                return ['success' => false, 'message' => 'No Paybill transaction found for this receipt number'];
            }

            $existingPayment = $this->db->fetch(
                "SELECT * FROM payments WHERE mpesa_receipt_number = :receipt LIMIT 1",
                ['receipt' => $transId]
            );

            if ($existingPayment) {
                if ($existingPayment['status'] === 'completed') {
                    return [
                        'success' => true,
                        'message' => 'Payment already verified and completed',
                        'payment_id' => $existingPayment['id'],
                        'already_verified' => true
                    ];
                }

                if ($memberId && empty($existingPayment['member_id'])) {
                    $this->db->execute(
                        "UPDATE payments SET member_id = :member_id WHERE id = :payment_id",
                        ['member_id' => $memberId, 'payment_id' => $existingPayment['id']]
                    );
                }

                if (!empty($paymentType) && empty($existingPayment['payment_type'])) {
                    $this->db->execute(
                        "UPDATE payments SET payment_type = :payment_type WHERE id = :payment_id",
                        ['payment_type' => $paymentType, 'payment_id' => $existingPayment['id']]
                    );
                }

                $this->paymentModel->confirmPayment($existingPayment['id'], $transId);

                $this->db->execute(
                    "UPDATE payments SET reconciliation_status = 'manual', reconciled_at = NOW(), reconciled_by = :user_id, reconciliation_notes = :notes WHERE id = :payment_id",
                    ['user_id' => $userId, 'notes' => $notes, 'payment_id' => $existingPayment['id']]
                );

                $this->logReconciliation($existingPayment['id'], [
                    'action' => 'manual_verify',
                    'previous_status' => 'unmatched',
                    'new_status' => 'manual',
                    'matched_member_id' => $memberId ?: $existingPayment['member_id'],
                    'match_method' => 'paybill_receipt',
                    'confidence_score' => 100,
                    'reconciled_by' => $userId,
                    'notes' => $notes
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment verified and completed',
                    'payment_id' => $existingPayment['id']
                ];
            }

            if (!$memberId) {
                return ['success' => false, 'message' => 'Member ID is required to post this payment'];
            }

            $transactionDate = $this->parseTransTime($callback['trans_time'] ?? '');
            $senderPhone = $this->formatPhoneNumber($callback['msisdn'] ?? '');
            $senderName = trim(
                ($callback['first_name'] ?? '') . ' ' .
                ($callback['middle_name'] ?? '') . ' ' .
                ($callback['last_name'] ?? '')
            );

            $paymentId = $this->paymentModel->recordPayment([
                'member_id' => $memberId,
                'amount' => $callback['trans_amount'] ?? 0,
                'payment_type' => $paymentType ?: 'monthly',
                'payment_method' => 'mpesa',
                'status' => 'pending',
                'transaction_id' => $transId,
                'mpesa_receipt_number' => $transId,
                'payment_date' => $transactionDate,
                'transaction_date' => $transactionDate,
                'sender_phone' => $senderPhone,
                'sender_name' => $senderName,
                'paybill_account' => $callback['bill_ref_number'] ?? '',
                'notes' => $notes
            ]);

            $this->paymentModel->confirmPayment($paymentId, $transId);

            $this->db->execute(
                "UPDATE payments SET reconciliation_status = 'manual', reconciled_at = NOW(), reconciled_by = :user_id, reconciliation_notes = :notes WHERE id = :payment_id",
                ['user_id' => $userId, 'notes' => $notes, 'payment_id' => $paymentId]
            );

            $this->logReconciliation($paymentId, [
                'action' => 'manual_verify',
                'previous_status' => 'unmatched',
                'new_status' => 'manual',
                'matched_member_id' => $memberId,
                'match_method' => 'paybill_receipt',
                'confidence_score' => 100,
                'reconciled_by' => $userId,
                'notes' => $notes
            ]);

            if (!empty($callback['id'])) {
                $this->markCallbackProcessed($callback['id'], $paymentId);
            }

            return [
                'success' => true,
                'message' => 'Payment verified and posted successfully',
                'payment_id' => $paymentId
            ];
        } catch (Exception $e) {
            error_log('Paybill verify error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Verification failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get all unmatched payments for manual reconciliation
     */
    public function getUnmatchedPayments()
    {
        $query = "SELECT * FROM vw_unmatched_payments ORDER BY transaction_date DESC";
        return $this->db->fetchAll($query);
    }
    
    /**
     * Get potential member matches for unmatched payment
     */
    public function findPotentialMatches($paymentId)
    {
        $payment = $this->paymentModel->find($paymentId);
        if (!$payment) {
            return [];
        }
        
        $matches = [];
        
        // Search by paybill account (ID number or member number)
        if (!empty($payment['paybill_account'])) {
            $query = "SELECT *, 'id_number' as match_type, 95 as confidence 
                      FROM members 
                      WHERE id_number = :id_number
                      UNION
                      SELECT *, 'member_number' as match_type, 90 as confidence 
                      FROM members 
                      WHERE member_number = :member_number";
            
            $results = $this->db->fetchAll($query, [
                'id_number' => $payment['paybill_account'],
                'member_number' => $payment['paybill_account']
            ]);
            $matches = array_merge($matches, $results);
        }
        
        // Search by phone number
        if (!empty($payment['sender_phone'])) {
            $query = "SELECT m.*, 'phone' as match_type, 70 as confidence 
                      FROM members m
                      JOIN users u ON m.user_id = u.id
                      WHERE u.phone = :phone";
            
            $results = $this->db->fetchAll($query, ['phone' => $payment['sender_phone']]);
            $matches = array_merge($matches, $results);
        }
        
        // Search by name (fuzzy match)
        if (!empty($payment['sender_name'])) {
            $nameParts = explode(' ', $payment['sender_name']);
            if (count($nameParts) >= 2) {
                $query = "SELECT m.*, 'name' as match_type, 60 as confidence 
                          FROM members m
                          JOIN users u ON m.user_id = u.id
                          WHERE (u.first_name LIKE :first OR u.last_name LIKE :last)
                          LIMIT 5";
                
                $results = $this->db->fetchAll($query, [
                    'first' => '%' . $nameParts[0] . '%',
                    'last' => '%' . end($nameParts) . '%'
                ]);
                $matches = array_merge($matches, $results);
            }
        }
        
        // Remove duplicates and sort by confidence
        $uniqueMatches = [];
        foreach ($matches as $match) {
            $key = $match['id'];
            if (!isset($uniqueMatches[$key]) || $match['confidence'] > $uniqueMatches[$key]['confidence']) {
                $uniqueMatches[$key] = $match;
            }
        }
        
        usort($uniqueMatches, function($a, $b) {
            return $b['confidence'] - $a['confidence'];
        });
        
        return array_values($uniqueMatches);
    }
    
    /**
     * Log reconciliation action
     */
    private function logReconciliation($paymentId, $data)
    {
        $query = "INSERT INTO payment_reconciliation_log (
            payment_id, action, previous_status, new_status,
            matched_member_id, match_method, confidence_score,
            reconciled_by, notes
        ) VALUES (
            :payment_id, :action, :previous_status, :new_status,
            :matched_member_id, :match_method, :confidence_score,
            :reconciled_by, :notes
        )";
        
        $params = [
            'payment_id' => $paymentId,
            'action' => $data['action'],
            'previous_status' => $data['previous_status'] ?? null,
            'new_status' => $data['new_status'] ?? null,
            'matched_member_id' => $data['matched_member_id'] ?? null,
            'match_method' => $data['match_method'] ?? null,
            'confidence_score' => $data['confidence_score'] ?? null,
            'reconciled_by' => $data['reconciled_by'] ?? null,
            'notes' => $data['notes'] ?? null
        ];
        
        $this->db->execute($query, $params);
    }
    
    /**
     * Mark callback as processed
     */
    private function markCallbackProcessed($callbackId, $paymentId)
    {
        $query = "UPDATE mpesa_c2b_callbacks SET 
                  processed = TRUE,
                  processed_at = NOW(),
                  payment_id = :payment_id
                  WHERE id = :callback_id";
        
        $this->db->execute($query, [
            'payment_id' => $paymentId,
            'callback_id' => $callbackId
        ]);
    }
    
    /**
     * Parse M-Pesa transaction time to MySQL datetime
     */
    private function parseTransTime($transTime)
    {
        // Format: 20260130143025 (YYYYMMDDHHmmss)
        if (strlen($transTime) == 14) {
            return substr($transTime, 0, 4) . '-' . 
                   substr($transTime, 4, 2) . '-' . 
                   substr($transTime, 6, 2) . ' ' . 
                   substr($transTime, 8, 2) . ':' . 
                   substr($transTime, 10, 2) . ':' . 
                   substr($transTime, 12, 2);
        }
        return date('Y-m-d H:i:s');
    }
    
    /**
     * Format phone number to standard Kenyan format
     */
    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) == 12 && substr($phone, 0, 3) == '254') {
            return '+' . $phone;
        } elseif (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            return '+254' . substr($phone, 1);
        } elseif (strlen($phone) == 9) {
            return '+254' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Get reconciliation statistics
     */
    public function getReconciliationStats()
    {
        $query = "SELECT 
            COUNT(*) as total_payments,
            SUM(CASE WHEN reconciliation_status = 'matched' THEN 1 ELSE 0 END) as matched,
            SUM(CASE WHEN reconciliation_status = 'unmatched' THEN 1 ELSE 0 END) as unmatched,
            SUM(CASE WHEN reconciliation_status = 'manual_match' THEN 1 ELSE 0 END) as `manual`,
            SUM(CASE WHEN auto_matched = TRUE THEN 1 ELSE 0 END) as auto_matched,
            SUM(amount) as total_amount,
            SUM(CASE WHEN reconciliation_status = 'matched' THEN amount ELSE 0 END) as matched_amount,
            SUM(CASE WHEN reconciliation_status = 'unmatched' THEN amount ELSE 0 END) as unmatched_amount
            FROM payments
            WHERE payment_method = 'mpesa' AND mpesa_receipt_number IS NOT NULL";
        
        return $this->db->fetch($query);
    }
}
