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
     * Last human-readable failure reason from manualReconciliation().
     */
    private $lastError = '';

    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Run a closure inside a transaction (re-uses an outer one if present).
     */
    private function inTransaction(callable $fn)
    {
        $pdo = $this->db->getConnection();
        $own = !$pdo->inTransaction();
        if ($own) {
            $pdo->beginTransaction();
        }
        try {
            $result = $fn();
            if ($own) {
                $pdo->commit();
            }
            return $result;
        } catch (Throwable $e) {
            if ($own && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Payment type implied by the member's current status.
     */
    public static function paymentTypeForStatus($status)
    {
        if ($status === 'inactive') {
            return 'registration';
        }
        if ($status === 'defaulted') {
            return 'reactivation';
        }
        return 'monthly';
    }

    /**
     * Compare the amount received with what the member is expected to pay.
     * Pure function (no DB) so it can be unit tested.
     *
     * - monthly: must equal members.monthly_contribution. If no contribution is
     *   on file the amount cannot be judged, so it is allowed but flagged.
     * - registration: must be at least REGISTRATION_FEE.
     * - reactivation: no fixed rule exists in the codebase, so it is allowed.
     *
     * @return array{ok:bool,expected:?float,unknown_expected:bool,reason:?string}
     */
    public static function evaluateAmount(array $member, $paymentType, $amount)
    {
        $amount = (float)$amount;

        if ($amount <= 0) {
            return ['ok' => false, 'expected' => null, 'unknown_expected' => false,
                'reason' => 'Amount received is zero or invalid'];
        }

        if ($paymentType === 'monthly') {
            $expected = (float)($member['monthly_contribution'] ?? 0);
            if ($expected <= 0) {
                return ['ok' => true, 'expected' => null, 'unknown_expected' => true, 'reason' => null];
            }
            if (abs($amount - $expected) < 0.005) {
                return ['ok' => true, 'expected' => $expected, 'unknown_expected' => false, 'reason' => null];
            }
            return ['ok' => false, 'expected' => $expected, 'unknown_expected' => false,
                'reason' => sprintf('Amount KES %s differs from expected monthly contribution KES %s',
                    number_format($amount, 2), number_format($expected, 2))];
        }

        if ($paymentType === 'registration') {
            $fee = (float)(defined('REGISTRATION_FEE') ? REGISTRATION_FEE : 200);
            if ($amount + 0.005 >= $fee) {
                return ['ok' => true, 'expected' => $fee, 'unknown_expected' => false, 'reason' => null];
            }
            return ['ok' => false, 'expected' => $fee, 'unknown_expected' => false,
                'reason' => sprintf('Amount KES %s is below the registration fee KES %s',
                    number_format($amount, 2), number_format($fee, 2))];
        }

        return ['ok' => true, 'expected' => null, 'unknown_expected' => true, 'reason' => null];
    }

    /**
     * Decide whether a candidate match may be posted without a human.
     * Phone-only evidence never auto-posts: phones are shared within families
     * and payers often pay for someone else.
     *
     * @return array{auto_post:bool,reason:?string}
     */
    public static function decideAutoPost($matchMethod, array $amountCheck)
    {
        if ($matchMethod === 'auto_phone') {
            return ['auto_post' => false,
                'reason' => 'Matched by phone number only - needs admin confirmation'];
        }
        if (empty($amountCheck['ok'])) {
            return ['auto_post' => false, 'reason' => $amountCheck['reason'] ?? 'Amount check failed'];
        }
        return ['auto_post' => true, 'reason' => null];
    }

    /**
     * Process M-Pesa C2B callback from Paybill
     * @param array $callbackData Raw callback data from M-Pesa
     * @return array Result with success status and payment_id if matched
     */
    public function processC2BCallback($callbackData)
    {
        $callbackId = null;

        try {
            $transId = trim((string)($callbackData['TransID'] ?? ''));
            $transTime = $callbackData['TransTime'] ?? '';
            $transAmount = $callbackData['TransAmount'] ?? 0;
            $billRefNumber = $callbackData['BillRefNumber'] ?? ''; // Member's ID number or member number
            $msisdn = $callbackData['MSISDN'] ?? '';
            $firstName = $callbackData['FirstName'] ?? '';
            $middleName = $callbackData['MiddleName'] ?? '';
            $lastName = $callbackData['LastName'] ?? '';

            if ($transId === '') {
                return ['success' => false, 'message' => 'Callback has no TransID'];
            }

            $existing = $this->db->fetch(
                "SELECT id, processed, payment_id FROM mpesa_c2b_callbacks WHERE trans_id = :trans_id",
                ['trans_id' => $transId]
            );

            if ($existing) {
                $linkedPayment = $this->paymentModel->findByReceiptNumber($transId);

                // Only treat as a duplicate when it really became a payment.
                if ($linkedPayment || !empty($existing['processed'])) {
                    if ($linkedPayment && empty($existing['processed'])) {
                        $this->markCallbackProcessed($existing['id'], $linkedPayment['id']);
                    }
                    return [
                        'success' => false,
                        'message' => 'Callback already processed',
                        'duplicate' => true
                    ];
                }

                // Stored on an earlier delivery but never turned into a payment: retry it.
                $callbackId = (int)$existing['id'];
            } else {
                $callbackId = (int)$this->storeCallback($callbackData);
            }

            $this->db->execute(
                "UPDATE mpesa_c2b_callbacks SET attempts = attempts + 1 WHERE id = :id",
                ['id' => $callbackId]
            );

            return $this->autoReconcilePayment([
                'trans_id' => $transId,
                'amount' => $transAmount,
                'transaction_date' => $this->parseTransTime($transTime),
                'sender_phone' => $this->formatPhoneNumber($msisdn),
                'sender_name' => trim("$firstName $middleName $lastName"),
                'bill_ref_number' => $billRefNumber,
                'callback_id' => $callbackId
            ]);

        } catch (Throwable $e) {
            error_log('C2B Callback Error: ' . $e->getMessage());

            // Two simultaneous deliveries: the UNIQUE index rejected the second one.
            if (stripos($e->getMessage(), 'Duplicate entry') !== false && $callbackId === null) {
                return ['success' => false, 'message' => 'Callback already processed', 'duplicate' => true];
            }

            if ($callbackId) {
                $this->recordCallbackFailure($callbackId, $e->getMessage());
            }

            return [
                'success' => false,
                'retryable' => $callbackId !== null,
                'message' => 'Failed to process callback: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Re-attempt callbacks that were stored but never became a payment.
     * Intended for cron (see cron/retry_unprocessed_c2b.php).
     *
     * @return array{attempted:int,recovered:int,failed:int}
     */
    public function retryUnprocessedCallbacks($limit = 50, $maxAttempts = 5)
    {
        $rows = $this->db->fetchAll(
            "SELECT id, raw_callback FROM mpesa_c2b_callbacks
             WHERE processed = 0 AND attempts < :max_attempts
               AND created_at < (NOW() - INTERVAL 2 MINUTE)
             ORDER BY id ASC LIMIT :row_limit",
            ['max_attempts' => (int)$maxAttempts, 'row_limit' => (int)$limit]
        );

        $summary = ['attempted' => 0, 'recovered' => 0, 'failed' => 0];
        foreach ($rows as $row) {
            $data = json_decode((string)$row['raw_callback'], true);
            if (!is_array($data)) {
                $this->recordCallbackFailure($row['id'], 'raw_callback is not valid JSON');
                $summary['failed']++;
                continue;
            }
            $summary['attempted']++;
            $result = $this->processC2BCallback($data);
            if (!empty($result['success']) || !empty($result['duplicate'])) {
                $summary['recovered']++;
            } else {
                $summary['failed']++;
            }
        }
        return $summary;
    }

    private function recordCallbackFailure($callbackId, $message)
    {
        try {
            $this->db->execute(
                "UPDATE mpesa_c2b_callbacks SET processing_error = :err WHERE id = :id",
                ['err' => substr((string)$message, 0, 1000), 'id' => $callbackId]
            );
        } catch (Throwable $e) {
            error_log('Could not record callback failure: ' . $e->getMessage());
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
     * Automatically reconcile payment with member account.
     *
     * Posting happens only when the evidence is strong (ID / member number)
     * AND the amount is what the member is expected to pay. Anything weaker is
     * recorded as 'unmatched' with a reason so an admin (optionally assisted by
     * the AI suggestion service) can decide.
     *
     * @param array $paymentData Payment details from callback
     * @return array Result with matched member info
     */
    public function autoReconcilePayment($paymentData)
    {
        $billRefNumber = self::normalizeAccountReference($paymentData['bill_ref_number'] ?? '');
        $senderPhone = $paymentData['sender_phone'] ?? '';

        $existingPayment = $this->paymentModel->findByReceiptNumber($paymentData['trans_id'] ?? '');
        if ($existingPayment) {
            if (isset($paymentData['callback_id'])) {
                $this->markCallbackProcessed($paymentData['callback_id'], $existingPayment['id']);
            }
            return [
                'success' => true,
                'matched' => !empty($existingPayment['member_id']),
                'payment_id' => $existingPayment['id'],
                'duplicate' => true,
                'message' => 'Payment receipt already recorded'
            ];
        }

        // Strategy 1: ID number (95) -> 2: member number (90) -> 3: phone (70, never auto-posts)
        $member = $this->memberModel->findByIdNumber($billRefNumber);
        $matchMethod = null;
        $confidence = 0;

        if ($member) {
            $matchMethod = 'auto_id_number';
            $confidence = 95;
        } else {
            $member = $this->memberModel->findByMemberNumber($billRefNumber);
            if ($member) {
                $matchMethod = 'auto_member_number';
                $confidence = 90;
            } else {
                $member = $this->memberModel->findByPhone($senderPhone);
                if ($member) {
                    $matchMethod = 'auto_phone';
                    $confidence = 70;
                }
            }
        }

        $paymentData['bill_ref_number'] = $billRefNumber;
        $reviewReason = null;

        if ($member && $confidence >= 70) {
            $paymentType = self::paymentTypeForStatus($member['status'] ?? '');
            $amountCheck = self::evaluateAmount($member, $paymentType, $paymentData['amount'] ?? 0);
            $decision = self::decideAutoPost($matchMethod, $amountCheck);

            if ($decision['auto_post']) {
                // All-or-nothing: payment row, membership side-effects, audit log
                // and callback link either all persist or none do.
                $paymentId = $this->inTransaction(function () use ($member, $paymentData, $paymentType, $matchMethod, $confidence) {
                    $paymentId = $this->createReconciledPayment($member['id'], $paymentData, true, $paymentType);

                    if ($paymentType === 'monthly') {
                        $this->memberModel->applySuccessfulMonthlyPayment(
                            $member['id'],
                            $paymentData['transaction_date'] ?? date('Y-m-d H:i:s')
                        );
                    } elseif ($paymentType === 'reactivation') {
                        $this->memberModel->reactivateMember($member['id']);
                    } elseif ($paymentType === 'registration') {
                        $this->paymentModel->activateMemberAfterRegistrationPayment((int)$member['id']);
                    }

                    $this->logReconciliation($paymentId, [
                        'action' => 'matched',
                        'previous_status' => 'pending',
                        'new_status' => 'matched',
                        'matched_member_id' => $member['id'],
                        'match_method' => $matchMethod,
                        'confidence_score' => $confidence,
                        'notes' => "Auto-matched via {$matchMethod}"
                    ]);

                    if (isset($paymentData['callback_id'])) {
                        $this->markCallbackProcessed($paymentData['callback_id'], $paymentId);
                    }

                    return $paymentId;
                });

                // After commit: an SMS failure must never undo a posted payment.
                $memberData = $this->memberModel->getMemberWithUser($member['id']);
                $confirmationPhone = $memberData['phone'] ?? $paymentData['sender_phone'] ?? '';
                $this->sendAutomaticPaymentConfirmation($confirmationPhone, $paymentData);

                return [
                    'success'      => true,
                    'matched'      => true,
                    'payment_id'   => $paymentId,
                    'member_id'    => $member['id'],
                    'member_name'  => ($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''),
                    'match_method' => $matchMethod,
                    'confidence'   => $confidence,
                    'payment_type' => $paymentType,
                ];
            }

            $reviewReason = sprintf('%s (candidate member #%d via %s)',
                $decision['reason'], (int)$member['id'], $matchMethod);
        }

        // No safe automatic match: record as unmatched with the reason.
        $paymentId = $this->inTransaction(function () use ($paymentData, $reviewReason) {
            $paymentId = $this->createUnmatchedPayment($paymentData, $reviewReason);

            $this->logReconciliation($paymentId, [
                'action' => $reviewReason ? 'review_required' : 'unmatched',
                'previous_status' => 'pending',
                'new_status' => 'unmatched',
                'match_method' => null,
                'notes' => $reviewReason ?: 'No member matched the account reference or phone'
            ]);

            if (isset($paymentData['callback_id'])) {
                $this->markCallbackProcessed($paymentData['callback_id'], $paymentId);
            }

            return $paymentId;
        });

        // Receipt confirmation is transactional, not a reconciliation action.
        $this->sendAutomaticPaymentConfirmation($paymentData['sender_phone'] ?? '', $paymentData);

        return [
            'success' => true,
            'matched' => false,
            'review_required' => $reviewReason !== null,
            'payment_id' => $paymentId,
            'message' => $reviewReason
                ? 'Payment recorded for admin review: ' . $reviewReason
                : 'Payment recorded as unmatched - requires manual reconciliation'
        ];
    }

    /**
     * Send the immediate transactional receipt confirmation. This deliberately
     * bypasses campaign draft/review/submit workflows.
     */
    private function sendAutomaticPaymentConfirmation($phone, array $paymentData)
    {
        $phone = trim((string)$phone);
        if ($phone === '') {
            return;
        }

        try {
            require_once ROOT_PATH . '/app/services/SmsService.php';
            $smsService = new SmsService();
            $smsService->sendPaymentConfirmationSms($phone, [
                'amount' => $paymentData['amount'],
                'transaction_id' => $paymentData['trans_id'],
            ]);
        } catch (Exception $notifEx) {
            // Payment processing must never be rolled back because an SMS provider failed.
            error_log('Automatic payment confirmation SMS error: ' . $notifEx->getMessage());
        }
    }

    public static function normalizeAccountReference($reference)
    {
        $reference = strtoupper(trim((string)$reference));
        $reference = preg_replace('/^(ID|NATIONALID|NATIONAL ID|NO|NUMBER)[:#\-\s]*/i', '', $reference);
        return preg_replace('/[^A-Z0-9]/', '', $reference);
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
    private function createUnmatchedPayment($paymentData, $notes = null)
    {
        // For unmatched payments, member_id is NULL - admin will manually assign later
        $query = "INSERT INTO payments (
            member_id, amount, payment_method, payment_date, status,
            reconciliation_status, reconciliation_notes, mpesa_receipt_number, transaction_date,
            sender_phone, sender_name, paybill_account, created_at
        ) VALUES (
            NULL, :amount, :payment_method, :payment_date, :status,
            :reconciliation_status, :reconciliation_notes, :mpesa_receipt_number, :transaction_date,
            :sender_phone, :sender_name, :paybill_account, NOW()
        )";

        $params = [
            'amount' => $paymentData['amount'],
            'payment_method' => 'mpesa',
            'payment_date' => $paymentData['transaction_date'],
            'status' => 'pending',
            'reconciliation_status' => 'unmatched',
            'reconciliation_notes' => $notes,
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
     * @param string $matchMethod 'manual' or 'ai_assisted' (admin-confirmed AI suggestion)
     * @return bool Success status (reason available via getLastError())
     */
    public function manualReconciliation($paymentId, $memberId, $userId, $notes = '', $matchMethod = 'manual')
    {
        $this->lastError = '';
        $matchMethod = in_array($matchMethod, ['manual', 'ai_assisted'], true) ? $matchMethod : 'manual';

        try {
            $member = $this->memberModel->find($memberId);
            if (!$member) {
                $this->lastError = 'Member not found';
                return false;
            }

            $payment = $this->paymentModel->find($paymentId);
            if (!$payment) {
                $this->lastError = 'Payment not found';
                return false;
            }

            // Re-assigning an already posted payment would extend coverage twice.
            $alreadyPosted = !empty($payment['member_id'])
                && (in_array($payment['reconciliation_status'] ?? '', ['matched', 'manual'], true)
                    || ($payment['status'] ?? '') === 'completed');
            if ($alreadyPosted) {
                $this->lastError = 'This payment is already reconciled to a member';
                return false;
            }

            $previousStatus = $payment['reconciliation_status'] ?? 'unmatched';
            $paymentType = $payment['payment_type'] ?? 'monthly';
            if ($previousStatus === 'unmatched' || empty($payment['member_id'])) {
                $paymentType = self::paymentTypeForStatus($member['status'] ?? '');
            }

            $this->inTransaction(function () use ($paymentId, $memberId, $userId, $notes, $matchMethod, $paymentType, $previousStatus, $payment) {
                $this->db->execute(
                    "UPDATE payments SET
                        member_id            = :member_id,
                        payment_type         = :payment_type,
                        reconciliation_status = 'manual',
                        reconciled_at        = NOW(),
                        reconciled_by        = :reconciled_by,
                        reconciliation_notes = :notes
                     WHERE id = :payment_id",
                    [
                        'member_id'    => $memberId,
                        'payment_type' => $paymentType,
                        'reconciled_by'=> $userId,
                        'notes'        => $notes,
                        'payment_id'   => $paymentId,
                    ]
                );

                // confirmPayment handles status=completed + membership side-effects
                $this->paymentModel->confirmPayment($paymentId);

                $this->logReconciliation($paymentId, [
                    'action'           => $matchMethod === 'ai_assisted' ? 'ai_assisted_match' : 'manual_match',
                    'previous_status'  => $previousStatus,
                    'new_status'       => 'manual',
                    'matched_member_id'=> $memberId,
                    'match_method'     => $matchMethod,
                    'confidence_score' => 100,
                    'reconciled_by'    => $userId,
                    'notes'            => $notes,
                ]);

                // Keep the raw callback in sync so it no longer looks pending.
                if (!empty($payment['mpesa_receipt_number'])) {
                    $this->db->execute(
                        "UPDATE mpesa_c2b_callbacks
                            SET processed = TRUE, processed_at = NOW(), payment_id = :payment_id
                          WHERE trans_id = :trans_id",
                        ['payment_id' => $paymentId, 'trans_id' => $payment['mpesa_receipt_number']]
                    );
                }
            });

            return true;
        } catch (Throwable $e) {
            error_log('Manual reconciliation error: ' . $e->getMessage());
            $this->lastError = 'Reconciliation failed; no changes were saved';
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
                if ($memberId && !empty($existingPayment['member_id']) && (int)$existingPayment['member_id'] !== (int)$memberId) {
                    return [
                        'success' => false,
                        'message' => 'This receipt is already assigned to another member'
                    ];
                }

                if ($memberId && (empty($existingPayment['member_id']) || (int)$existingPayment['member_id'] !== (int)$memberId)) {
                    $this->db->execute(
                        "UPDATE payments SET member_id = :member_id WHERE id = :payment_id",
                        ['member_id' => $memberId, 'payment_id' => $existingPayment['id']]
                    );
                    $existingPayment['member_id'] = $memberId;
                }

                if (!empty($paymentType) && ($existingPayment['payment_type'] ?? '') !== $paymentType) {
                    $this->db->execute(
                        "UPDATE payments SET payment_type = :payment_type WHERE id = :payment_id",
                        ['payment_type' => $paymentType, 'payment_id' => $existingPayment['id']]
                    );
                    $existingPayment['payment_type'] = $paymentType;
                }

                if ($existingPayment['status'] === 'completed') {
                    $this->paymentModel->confirmPayment($existingPayment['id'], $transId);

                    $this->db->execute(
                        "UPDATE payments SET reconciliation_status = 'manual', reconciled_at = NOW(), reconciled_by = :user_id, reconciliation_notes = :notes WHERE id = :payment_id",
                        ['user_id' => $userId, 'notes' => $notes, 'payment_id' => $existingPayment['id']]
                    );

                    if (!empty($callback['id'])) {
                        $this->markCallbackProcessed($callback['id'], $existingPayment['id']);
                    }

                    return [
                        'success' => true,
                        'message' => 'Payment already verified and completed; member records refreshed',
                        'payment_id' => $existingPayment['id'],
                        'already_verified' => true
                    ];
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
            return ['success' => false, 'message' => 'A payment verification service error occurred. Please check the code and try again.'];
        }
    }
    
    /**
     * Get all unmatched payments for manual reconciliation
     */
    public function getUnmatchedPayments(array $filters = [])
    {
        $query = "SELECT * FROM vw_unmatched_payments";
        $where = [];
        $params = [];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = "(mpesa_receipt_number LIKE :search_receipt
                OR paybill_account LIKE :search_account
                OR sender_name LIKE :search_sender
                OR sender_phone LIKE :search_phone)";
            $searchTerm = '%' . $search . '%';
            $params['search_receipt'] = $searchTerm;
            $params['search_account'] = $searchTerm;
            $params['search_sender'] = $searchTerm;
            $params['search_phone'] = $searchTerm;
        }

        $dateFrom = trim((string)($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $where[] = "DATE(transaction_date) >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        $dateTo = trim((string)($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $where[] = "DATE(transaction_date) <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $amountMin = trim((string)($filters['amount_min'] ?? ''));
        if ($amountMin !== '' && is_numeric($amountMin)) {
            $where[] = "amount >= :amount_min";
            $params['amount_min'] = (float)$amountMin;
        }

        $amountMax = trim((string)($filters['amount_max'] ?? ''));
        if ($amountMax !== '' && is_numeric($amountMax)) {
            $where[] = "amount <= :amount_max";
            $params['amount_max'] = (float)$amountMax;
        }

        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        $query .= " ORDER BY transaction_date DESC";
        return $this->db->fetchAll($query, $params);
    }

    public function getRecentReconciliationLogs($limit = 10)
    {
        $query = "SELECT
                l.*,
                p.mpesa_receipt_number,
                p.amount,
                p.reconciliation_notes,
                p.reconciled_at,
                m.member_number,
                u.first_name,
                u.last_name,
                admin.first_name AS admin_first_name,
                admin.last_name AS admin_last_name
            FROM payment_reconciliation_log l
            LEFT JOIN payments p ON l.payment_id = p.id
            LEFT JOIN members m ON l.matched_member_id = m.id
            LEFT JOIN users u ON m.user_id = u.id
            LEFT JOIN users admin ON l.reconciled_by = admin.id
            ORDER BY l.created_at DESC
            LIMIT :limit";

        return $this->db->fetchAll($query, ['limit' => (int)$limit]);
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
            $normalizedAccount = self::normalizeAccountReference($payment['paybill_account']);
            $query = "SELECT *, 'id_number' as match_type, 95 as confidence 
                      FROM members 
                      WHERE id_number = :id_number
                      UNION
                      SELECT *, 'member_number' as match_type, 90 as confidence 
                      FROM members 
                      WHERE member_number = :member_number";
            
            $results = $this->db->fetchAll($query, [
                'id_number' => $normalizedAccount,
                'member_number' => $normalizedAccount
            ]);
            $matches = array_merge($matches, $results);
        }
        
        // Search by phone number
        if (!empty($payment['sender_phone'])) {
            $memberByPhone = $this->memberModel->findByPhone($payment['sender_phone']);
            if ($memberByPhone) {
                $memberByPhone['match_type'] = 'phone';
                $memberByPhone['confidence'] = 70;
                $matches[] = $memberByPhone;
            }
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
        return formatKenyanPhone($phone);
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
            SUM(CASE WHEN reconciliation_status = 'manual' THEN 1 ELSE 0 END) as `manual`,
            SUM(CASE WHEN auto_matched = TRUE THEN 1 ELSE 0 END) as auto_matched,
            SUM(amount) as total_amount,
            SUM(CASE WHEN reconciliation_status = 'matched' THEN amount ELSE 0 END) as matched_amount,
            SUM(CASE WHEN reconciliation_status = 'unmatched' THEN amount ELSE 0 END) as unmatched_amount
            FROM payments
            WHERE payment_method = 'mpesa' AND mpesa_receipt_number IS NOT NULL";
        
        return $this->db->fetch($query);
    }
}
