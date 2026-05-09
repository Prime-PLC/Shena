<?php
/**
 * Payment Service - Handles M-Pesa and other payment integrations
 */
class PaymentService 
{
    private $config;
    
    public function __construct()
    {
        $this->config = [
            'consumer_key' => MPESA_CONSUMER_KEY,
            'consumer_secret' => MPESA_CONSUMER_SECRET,
            'business_shortcode' => MPESA_BUSINESS_SHORTCODE,
            'passkey' => MPESA_PASSKEY,
            'callback_url' => MPESA_CALLBACK_URL
        ];
    }
    
    public function getAccessToken()
    {
        try {
            // Use environment-specific API URL
            $baseUrl = MPESA_ENVIRONMENT === 'production' 
                ? 'https://api.safaricom.co.ke' 
                : 'https://sandbox.safaricom.co.ke';
            
            $url = $baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
            
            $credentials = base64_encode($this->config['consumer_key'] . ':' . $this->config['consumer_secret']);
            
            error_log("Getting M-Pesa token from: {$url}");
            error_log("Using credentials: " . substr($credentials, 0, 20) . "...");
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . $credentials,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, MPESA_ENVIRONMENT === 'production');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $data = json_decode($response, true);
                $token = $data['access_token'] ?? false;
                if ($token) {
                    error_log("✅ M-Pesa token obtained: " . substr($token, 0, 20) . "...");
                }
                return $token;
            }
            
            error_log("❌ M-Pesa token error: HTTP {$httpCode}, Response: {$response}, CURL Error: {$curlError}");
            return false;
            
        } catch (Exception $e) {
            error_log('M-Pesa access token error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function initiateSTKPush($phoneNumber, $amount, $memberNumber, $description = 'Monthly Contribution')
    {
        try {
            error_log("=== Starting STK Push Process ===");
            error_log("Phone: {$phoneNumber}, Amount: {$amount}, Member: {$memberNumber}");
            
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                throw new Exception('Failed to get M-Pesa access token');
            }
            
            error_log("Access token received for STK push");
            
            // Format phone number to 254XXXXXXXXX
            $phone = $this->formatPhoneNumber($phoneNumber);
            if (!$phone) {
                throw new Exception('Invalid phone number format');
            }
            
            error_log("Formatted phone: {$phone}");
            
            $timestamp = date('YmdHis');
            $password = base64_encode($this->config['business_shortcode'] . $this->config['passkey'] . $timestamp);
            
            $data = [
                'BusinessShortCode' => $this->config['business_shortcode'],
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int)$amount,
                'PartyA' => $phone,
                'PartyB' => $this->config['business_shortcode'],
                'PhoneNumber' => $phone,
                'CallBackURL' => $this->config['callback_url'],
                'AccountReference' => $memberNumber,
                'TransactionDesc' => $description
            ];
            
            error_log("STK Push payload: " . json_encode($data));
            
            // Use environment-specific API URL
            $baseUrl = MPESA_ENVIRONMENT === 'production' 
                ? 'https://api.safaricom.co.ke' 
                : 'https://sandbox.safaricom.co.ke';
            
            $url = $baseUrl . '/mpesa/stkpush/v1/processrequest';
            
            error_log("Initiating STK Push to {$url} for {$phone}, Amount: {$amount}, Shortcode: {$this->config['business_shortcode']}");
            error_log("Using token: " . substr($accessToken, 0, 20) . "...");
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, MPESA_ENVIRONMENT === 'production');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            error_log("STK Push Response: HTTP {$httpCode}, Body: {$response}");
            
            if ($httpCode == 200) {
                $result = json_decode($response, true);
                if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                    error_log("✅ STK Push successful: " . json_encode($result));
                    return $result;
                } else {
                    error_log('❌ M-Pesa STK Push failed: ' . $response);
                    return false;
                }
            } else {
                error_log("❌ M-Pesa STK Push HTTP Error {$httpCode}: {$response}, CURL: {$curlError}");
                return false;
            }
            
        } catch (Exception $e) {
            error_log('❌ M-Pesa STK Push error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle different formats
        if (strlen($phone) == 9) {
            // 712345678 -> 254712345678
            return '254' . $phone;
        } elseif (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            // 0712345678 -> 254712345678
            return '254' . substr($phone, 1);
        } elseif (strlen($phone) == 12 && substr($phone, 0, 3) == '254') {
            // 254712345678 -> 254712345678
            return $phone;
        }
        
        return false;
    }
    
    public function processCallback($callbackData)
    {
        try {
            $paymentModel = new Payment();
            
            // Parse the callback data
            $body = $callbackData['Body'] ?? [];
            $stkCallback = $body['stkCallback'] ?? [];
            
            $merchantRequestId = $stkCallback['MerchantRequestID'] ?? '';
            $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? '';
            $resultCode = $stkCallback['ResultCode'] ?? '';
            $resultDesc = $stkCallback['ResultDesc'] ?? '';
            
            if ($resultCode == 0) {
                // Payment successful
                $callbackMetadata = $stkCallback['CallbackMetadata'] ?? [];
                $items = $callbackMetadata['Item'] ?? [];
                
                $amount = 0;
                $mpesaReceiptNumber = '';
                $phoneNumber = '';
                $transactionDate = '';
                
                foreach ($items as $item) {
                    switch ($item['Name']) {
                        case 'Amount':
                            $amount = $item['Value'];
                            break;
                        case 'MpesaReceiptNumber':
                            $mpesaReceiptNumber = $item['Value'];
                            break;
                        case 'PhoneNumber':
                            $phoneNumber = $item['Value'];
                            break;
                        case 'TransactionDate':
                            $transactionDate = $item['Value'];
                            break;
                    }
                }
                
                // Find the payment record by checkout request ID
                $payment = $paymentModel->findAll(['transaction_reference' => $checkoutRequestId]);
                
                if (!empty($payment)) {
                    $paymentId = $payment[0]['id'];

                    if (($payment[0]['status'] ?? '') === 'completed' && !empty($payment[0]['mpesa_receipt_number'])) {
                        return ['status' => 'success', 'message' => 'Payment already processed'];
                    }
                    
                    // Update payment status
                    $paymentModel->confirmPayment($paymentId, $mpesaReceiptNumber);
                    $paymentModel->update($paymentId, [
                        'transaction_date' => $transactionDate ?: date('Y-m-d H:i:s'),
                        'payment_date' => $transactionDate ?: date('Y-m-d H:i:s'),
                        'sender_phone' => $phoneNumber,
                        'reconciliation_status' => 'matched',
                        'auto_matched' => 1,
                        'reconciled_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Retrieve updated payment record to check type and member
                    $confirmedPayment = $paymentModel->find($paymentId);
                    $memberId = $confirmedPayment['member_id'];
                    $paymentType = $confirmedPayment['payment_type'] ?? 'monthly';
                    
                    // Get member and user models
                    $memberModel = new Member();
                    $member = $memberModel->find($memberId);
                    
                    // -- Handle Registration Fee Payment & Agent Commission --
                    if ($member && $paymentType === 'registration') {
                        $registrationFeeRequired = defined('REGISTRATION_FEE') ? REGISTRATION_FEE : 200;
                        $allRegistrationPayments = $paymentModel->findAll([
                            'member_id' => $memberId,
                            'payment_type' => 'registration',
                            'status' => 'completed'
                        ]);
                        $totalPaid = 0;
                        foreach ($allRegistrationPayments as $regPayment) {
                            $totalPaid += $regPayment['amount'];
                        }
                        // Auto-activate if full registration fee is paid
                        if ($totalPaid >= $registrationFeeRequired && $member['status'] === 'inactive') {
                            $memberModel->update($memberId, [
                                'status' => 'active',
                                'coverage_ends' => date('Y-m-d', strtotime('+1 year'))
                            ]);
                            // Also activate user account
                            $userModel = new User();
                            $userModel->update($member['user_id'], ['status' => 'active']);
                            error_log("Member #{$memberId} automatically activated after registration fee payment of KES {$totalPaid}.");
                        }
                        // Award agent commission for registration (KES 200, one-time, only if not already awarded)
                        if (!empty($member['agent_id'])) {
                            $agentModel = new Agent();
                            // Check if commission already exists for this member registration
                            $existing = $agentModel->getAgentCommissions($member['agent_id'], ['status' => '', 'member_id' => $memberId, 'commission_type' => 'registration']);
                            if (empty($existing)) {
                                $agentModel->recordCommission([
                                    'agent_id' => $member['agent_id'],
                                    'member_id' => $memberId,
                                    'payment_id' => null,
                                    'commission_type' => 'registration',
                                    'amount' => 200,
                                    'commission_rate' => 100,
                                    'commission_amount' => 200,
                                    'status' => 'pending'
                                ]);
                                error_log("Agent {$member['agent_id']} awarded KES 200 commission for member registration #{$memberId}.");
                            }
                        }
                    }
                    
                    // -- Automate Reactivation Logic --
                    // Check if member is defaulted or payment is explicitly for reactivation
                    if ($member && ($member['status'] === 'defaulted' || $paymentType === 'reactivation')) {
                         // Calculate Arrears
                         // Arrears = (Months since coverage ended) * Monthly Contribution
                         // But simple policy check: Pay Arrears + Reactivation Fee (100)
                         
                         // We need to calculate how many months missed.
                         // Coverage ends date vs Today.
                         $today = new DateTimeImmutable('today');
                         $coverageEnds = $member['coverage_ends'] ? new DateTimeImmutable($member['coverage_ends']) : $today;
                         
                         // If coverage ends is in future/today, maybe not defaulted? 
                         // But Status says defaulted.
                         
                         $monthsMissed = 0;
                         if ($coverageEnds < $today) {
                             $diff = $today->diff($coverageEnds);
                             $monthsMissed = ($diff->y * 12) + $diff->m + ($diff->d > 0 ? 1 : 0);
                         }
                         
                         // If defaulted, at least 2 months missed typically?
                         if ($monthsMissed < 0) $monthsMissed = 0;
                         
                         // Enforce minimum arrears calculation if detailed logic needed
                         // For now, valid payment check:
                         $reactivationFee = defined('REACTIVATION_FEE') ? REACTIVATION_FEE : 100;
                         $monthlyContribution = $member['monthly_contribution'];
                         
                         $arrearsAmount = $monthsMissed * $monthlyContribution;
                         $totalRequired = $arrearsAmount + $reactivationFee;
                         
                         // Allow small variance or just check if payment covers significant portion?
                         // Policy says "Pay all outstanding contributions plus reactivation fee".
                         // If the user initiates a specific "Reactivation" payment, they should have been quoted this amount.
                         // We assume the amount paid ($amount) is what was requested.
                         
                         // We verify if this amount is roughly sufficient (e.g. within 100 bob or exact?)
                         // Let's be strict but safe: check if type is reactivation OR (defaulted AND amount big enough)
                         
                         if ($paymentType === 'reactivation' || ($member['status'] === 'defaulted' && $amount >= $totalRequired)) {
                             $memberModel->reactivateMember($memberId);
                             error_log("Member #{$memberId} automatically reactivated after payment of {$amount}.");
                         }
                    }
                    
                    // No commission for monthly payments anymore
                    // if ($paymentType === 'monthly') {
                    //     $this->recordCommissionForPayment($memberId, $paymentId, $amount);
                    // }

                    // Send confirmation SMS and email
                    $this->sendPaymentConfirmation($payment[0], $amount, $mpesaReceiptNumber);

                    return ['status' => 'success', 'message' => 'Payment processed successfully'];
                }
            } else {
                // Payment failed
                $payment = $paymentModel->findAll(['transaction_reference' => $checkoutRequestId]);
                
                if (!empty($payment)) {
                    $paymentId = $payment[0]['id'];
                    $paymentModel->failPayment($paymentId, $resultDesc);
                }
                
                return ['status' => 'failed', 'message' => $resultDesc];
            }
            
        } catch (Exception $e) {
            error_log('M-Pesa callback processing error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Callback processing failed'];
        }
    }
    
    public function recordPaymentAttempt($memberId, $amount, $phoneNumber, $checkoutRequestId, $paymentType = 'monthly')
    {
        $paymentModel = new Payment();
        
        return $paymentModel->recordPayment([
            'member_id' => $memberId,
            'amount' => $amount,
            'payment_type' => $paymentType,
            'payment_method' => 'mpesa',
            'phone_number' => $phoneNumber,
            'status' => 'pending',
            'transaction_reference' => $checkoutRequestId
        ]);
    }
    
    public function queryTransactionStatus($checkoutRequestId)
    {
        try {
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                return false;
            }
            
            $timestamp = date('YmdHis');
            $password = base64_encode($this->config['business_shortcode'] . $this->config['passkey'] . $timestamp);
            
            $data = [
                'BusinessShortCode' => $this->config['business_shortcode'],
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId
            ];
            
            // Use environment-specific API URL
            $baseUrl = MPESA_ENVIRONMENT === 'production' 
                ? 'https://api.safaricom.co.ke' 
                : 'https://sandbox.safaricom.co.ke';
            
            $url = $baseUrl . '/mpesa/stkpushquery/v1/query';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, MPESA_ENVIRONMENT === 'production');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                return json_decode($response, true);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('M-Pesa query error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function sendPaymentConfirmation($payment, $amount, $transactionId)
    {
        try {
            // Get member details
            $memberModel = new Member();
            $member = $memberModel->getMemberWithUser($payment['member_id']);
            
            if (!$member) {
                return;
            }
            
            // Send email confirmation
            $emailService = new EmailService();
            $emailService->sendPaymentConfirmationEmail($member['email'], [
                'name' => $member['first_name'] . ' ' . $member['last_name'],
                'amount' => $amount,
                'transaction_id' => $transactionId,
                'payment_date' => date('Y-m-d H:i:s')
            ]);
            
            // Send SMS confirmation
            $smsService = new SmsService();
            $smsService->sendPaymentConfirmationSms($member['phone'], [
                'amount' => $amount,
                'transaction_id' => $transactionId
            ]);
            
        } catch (Exception $e) {
            error_log('Payment confirmation error: ' . $e->getMessage());
        }
    }
    
    public function processManualPayment($memberId, $amount, $paymentMethod, $reference, $notes = null)
    {
        $paymentModel = new Payment();

        return $paymentModel->recordPayment([
            'member_id' => $memberId,
            'amount' => $amount,
            'payment_type' => 'monthly',
            'payment_method' => $paymentMethod,
            'status' => 'completed',
            'reference' => $reference,
            'notes' => $notes,
            'payment_date' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Record commission for agent when member makes a payment
     */
    private function recordCommissionForPayment($memberId, $paymentId, $amount)
    {
        try {
            // Get member details to find agent
            $memberModel = new Member();
            $member = $memberModel->find($memberId);

            if (!$member || empty($member['agent_id'])) {
                return; // No agent assigned
            }

            $agentId = $member['agent_id'];

            // Get agent details for commission rate
            $agentModel = new Agent();
            $agent = $agentModel->getAgentById($agentId);

            if (!$agent || empty($agent['commission_rate'])) {
                return; // No commission rate set
            }

            $commissionRate = $agent['commission_rate'];
            $commissionAmount = ($amount * $commissionRate) / 100;

            // Record the commission
            $agentModel->recordCommission([
                'agent_id' => $agentId,
                'member_id' => $memberId,
                'payment_id' => $paymentId,
                'commission_type' => 'monthly_contribution',
                'amount' => $amount,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'status' => 'pending' // Commissions need admin approval
            ]);

            error_log("Commission recorded for agent {$agentId}: KES {$commissionAmount} on payment of KES {$amount}");

            // Send notification to agent
            $this->sendAgentNotification(
                $agent['user_id'],
                'Commission Earned',
                "You've earned a commission of KES " . number_format($commissionAmount, 2) . " from a member payment of KES " . number_format($amount, 2) . ". The commission is pending approval.",
                '/agent/commissions',
                'View Commissions'
            );

        } catch (Exception $e) {
            error_log('Commission recording error: ' . $e->getMessage());
        }
    }

    /**
     * Send in-app notification to an agent
     */
    private function sendAgentNotification($userId, $subject, $message, $actionUrl = null, $actionText = null)
    {
        try {
            require_once __DIR__ . '/InAppNotificationService.php';
            $notificationService = new InAppNotificationService();
            $notificationService->notifyUser($userId, [
                'subject' => $subject,
                'message' => $message,
                'action_url' => $actionUrl,
                'action_text' => $actionText
            ]);
        } catch (Exception $e) {
            error_log('Agent notification error: ' . $e->getMessage());
        }
    }
}
