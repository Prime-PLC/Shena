<?php
/**
 * SMS Service - Handles SMS sending via HostPinnacle
 */
class SmsService
{
    private $config;
    private $apiUrl;
    private $baseUrl;
    
    public function __construct()
    {
        $this->config = [
            'user_id' => HOSTPINNACLE_USER_ID,
            'api_key' => HOSTPINNACLE_API_KEY,
            'sender_id' => HOSTPINNACLE_SENDER_ID
        ];
        $this->apiUrl = defined('HOSTPINNACLE_SMS_API_URL') ? HOSTPINNACLE_SMS_API_URL : 'https://smsportal.hostpinnacle.co.ke/SMSApi/send';
        $this->baseUrl = preg_replace('#/SMSApi/.*$#', '', $this->apiUrl) ?: 'https://smsportal.hostpinnacle.co.ke';
    }
    
    public function sendSms($to, $message)
    {
        try {
            // Validate phone presence
            if (empty($to)) {
                error_log('SMS not sent: recipient phone is empty');
                return ['success' => false, 'error' => 'empty_recipient'];
            }
            // Check if SMS credentials are configured
            if (empty($this->config['user_id']) || empty($this->config['api_key'])) {
                error_log('SMS not sent: HostPinnacle credentials not configured');
                return ['success' => false, 'error' => 'SMS credentials not configured'];
            }
            
            // Format phone number for Kenyan numbers
            $to = $this->formatPhoneNumber($to);

            // Validate formatted number
            if (!$this->validatePhoneNumber($to)) {
                error_log('SMS not sent: invalid phone number ' . $to);
                return ['success' => false, 'error' => 'invalid_phone'];
            }
            
            // HostPinnacle API endpoint
            $url = $this->apiUrl;
            
            // HostPinnacle API parameters
            $data = [
                'userid' => $this->config['user_id'],
                'password' => $this->config['api_key'],
                'mobile' => $to,
                'msg' => $message,
                'senderid' => $this->config['sender_id'],
                'sendMethod' => 'quick',
                'msgType' => 'text',
                'duplicatecheck' => 'true',
                'output' => 'json'
            ];
            
            $request = $this->postForm($url, $data);
            $response = $request['body'];
            $httpCode = $request['http_code'];

            if ($httpCode == 200) {
                $result = json_decode($response, true);
                if (!is_array($result)) {
                    error_log('SMS sending failed: non-JSON response ' . $response);
                    return ['success' => false, 'submitted' => false, 'error' => 'Provider returned an unreadable response', 'raw_response' => $response];
                }

                // HostPinnacle returns {"status":"success","transactionId":"..."} on success
                // (older docs listed status:"200" — accept both for safety)
                $statusVal = strtolower((string)($result['status'] ?? ''));
                if ($statusVal === 'success' || $statusVal === '200' || !empty($result['transactionId'])) {
                    return [
                        'success' => true,
                        'submitted' => true,
                        'status' => 'submitted',
                        'provider_message_id' => $result['transactionId'] ?? $result['transaction_id'] ?? null,
                        'provider_status' => $result['status'] ?? $result['statusCode'] ?? 'submitted',
                        'provider_cause' => $result['reason'] ?? $result['message'] ?? null,
                        'data' => $result
                    ];
                } else {
                    error_log('SMS sending failed: ' . $response);
                    $reason = $result['reason'] ?? $result['message'] ?? 'Unknown error';
                    return ['success' => false, 'error' => 'SMS failed: ' . $reason, 'data' => $result];
                }
            } else {
                error_log('SMS sending failed: HTTP Code ' . $httpCode . ', Response: ' . $response);
                return ['success' => false, 'error' => 'HTTP Error ' . $httpCode];
            }
            
        } catch (Exception $e) {
            error_log('SMS sending error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'SMS delivery service is temporarily unavailable.'];
        }
    }

    public function checkDeliveryStatus($transactionId)
    {
        if (empty($transactionId)) {
            return ['success' => false, 'error' => 'missing_transaction_id'];
        }
        if (empty($this->config['user_id']) || empty($this->config['api_key'])) {
            return ['success' => false, 'error' => 'SMS credentials not configured'];
        }

        $url = $this->baseUrl . '/SMSApi/reports/status';
        $params = [
            'userid' => $this->config['user_id'],
            'password' => $this->config['api_key'],
            'transactionId' => $transactionId,
            'output' => 'json'
        ];

        try {
            $request = $this->postForm($url, $params);
            $response = $request['body'];
            $result = json_decode($response, true);

            if ($request['http_code'] !== 200 || !is_array($result)) {
                return [
                    'success' => false,
                    'error' => 'Unable to read delivery report',
                    'http_code' => $request['http_code'],
                    'raw_response' => $response
                ];
            }

            $providerStatus = $this->extractProviderStatus($result);
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => $this->normalizeProviderStatus($providerStatus),
                'provider_status' => $providerStatus,
                'provider_cause' => $this->extractProviderCause($result),
                'delivered_at' => $this->extractDeliveredAt($result),
                'data' => $result
            ];
        } catch (Exception $e) {
            error_log('SMS DLR check failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Delivery report check failed'];
        }
    }

    public function getAccountStatus()
    {
        if (empty($this->config['user_id']) || empty($this->config['api_key'])) {
            return ['success' => false, 'error' => 'SMS credentials not configured'];
        }

        $url = $this->baseUrl . '/SMSApi/account/readstatus';
        $params = [
            'userid' => $this->config['user_id'],
            'password' => $this->config['api_key'],
            'output' => 'json'
        ];

        try {
            $request = $this->postForm($url, $params, 8);
            $result = json_decode($request['body'], true);
            if ($request['http_code'] !== 200 || !is_array($result)) {
                return ['success' => false, 'error' => 'Unable to read SMS account status', 'raw_response' => $request['body']];
            }

            return [
                'success' => true,
                'sms_balance' => $result['smsBalance'] ?? $result['balance'] ?? null,
                'data' => $result
            ];
        } catch (Exception $e) {
            error_log('SMS account status check failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Unable to read SMS account status'];
        }
    }

    public function normalizeProviderStatus($status)
    {
        $value = strtolower(trim((string) $status));
        $value = str_replace([' ', '-'], '_', $value);

        if (in_array($value, ['delivered', 'success', 'successful', 'delivrd'], true)) {
            return 'delivered';
        }
        if (in_array($value, ['submitted', 'sent', 'accepted', 'queued', 'pending', 'in_progress'], true)) {
            return 'submitted';
        }
        if (in_array($value, ['undelivered', 'failed', 'failure', 'not_sent', 'notsent'], true)) {
            return 'undelivered';
        }
        if (in_array($value, ['expired', 'timeout', 'timed_out'], true)) {
            return 'expired';
        }
        if (in_array($value, ['rejected', 'blocked', 'blacklisted'], true)) {
            return 'rejected';
        }

        return $value === '' ? 'unknown' : 'unknown';
    }

    private function postForm($url, array $data, $timeout = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, max(5, (int) $timeout));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new Exception($curlError ?: 'Provider request failed');
        }

        return ['http_code' => $httpCode, 'body' => $body];
    }

    private function extractProviderStatus(array $result)
    {
        $keys = ['Status', 'status', 'deliveryStatus', 'delivery_status', 'messageStatus', 'message_status', 'statusCode'];
        foreach ($keys as $key) {
            if (isset($result[$key]) && $result[$key] !== '') {
                return (string) $result[$key];
            }
        }

        if (isset($result[0]) && is_array($result[0])) {
            return $this->extractProviderStatus($result[0]);
        }

        return 'unknown';
    }

    private function extractProviderCause(array $result)
    {
        $keys = ['Cause', 'cause', 'reason', 'Reason', 'message', 'Message', 'error', 'Error'];
        foreach ($keys as $key) {
            if (isset($result[$key]) && $result[$key] !== '') {
                return (string) $result[$key];
            }
        }

        if (isset($result[0]) && is_array($result[0])) {
            return $this->extractProviderCause($result[0]);
        }

        return null;
    }

    private function extractDeliveredAt(array $result)
    {
        $keys = ['DeliveredTime', 'deliveredTime', 'delivered_at', 'deliveryTime', 'DeliveryTime'];
        foreach ($keys as $key) {
            if (!empty($result[$key])) {
                $timestamp = strtotime((string) $result[$key]);
                return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
            }
        }

        if (isset($result[0]) && is_array($result[0])) {
            return $this->extractDeliveredAt($result[0]);
        }

        return null;
    }
    
    public function sendWelcomeSms($phone, $data)
    {
        $message = "Welcome to Shena Companion Welfare Association! Your member number is {$data['member_number']}. Thank you for joining us.";
        return $this->sendSms($phone, $message);
    }
    
    public function sendActivationSms($phone, $data)
    {
        $message = "Congratulations! Your Shena Companion account has been activated. Member No: {$data['member_number']}. You can now login to your dashboard.";
        return $this->sendSms($phone, $message);
    }
    
    public function sendPaymentReminderSms($phone, $data)
    {
        $message = "Payment Reminder: Your monthly contribution of KES {$data['amount']} is due. Pay via M-Pesa Paybill 4163987. Member: {$data['member_number']}";
        return $this->sendSms($phone, $message);
    }
    
    public function sendPaymentConfirmationSms($phone, $data)
    {
        $message = "Payment confirmed! KES {$data['amount']} received. Transaction ID: {$data['transaction_id']}. Thank you. - Shena Companion";
        return $this->sendSms($phone, $message);
    }
    
    public function sendClaimStatusSms($phone, $data)
    {
        $status = ucfirst($data['status']);
        $message = "Claim Update: Your claim has been {$status}. ";
        
        if ($data['status'] === 'approved' && isset($data['approved_amount'])) {
            $message .= "Approved amount: KES {$data['approved_amount']}. ";
        }
        
        $message .= "Check your dashboard for details. - Shena Companion";
        
        return $this->sendSms($phone, $message);
    }
    
    public function sendGracePeriodWarning($phone, $data)
    {
        $message = "Grace Period Warning: Your account will expire on {$data['expiry_date']}. Please make your payment to avoid deactivation. Member: {$data['member_number']}";
        return $this->sendSms($phone, $message);
    }
    
    public function sendAccountDeactivationSms($phone, $data)
    {
        $message = "Account Deactivated: Your membership has been suspended due to non-payment. Pay KES " . REACTIVATION_FEE . " reactivation fee + dues to restore. Member: {$data['member_number']}";
        return $this->sendSms($phone, $message);
    }
    
    public function sendBulkSms($recipients, $message)
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $phone = is_array($recipient) ? $recipient['phone'] : $recipient;
            $results[] = $this->sendSms($phone, $message);
            
            // Add delay to respect rate limits
            sleep(1);
        }
        
        return $results;
    }
    
    public function sendCustomMessage($phone, $message, $memberNumber = null)
    {
        if ($memberNumber) {
            $message .= " - Member: {$memberNumber}";
        }
        
        $message .= " - Shena Companion";
        
        return $this->sendSms($phone, $message);
    }
    
    public function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle Kenyan phone number formats
        // HostPinnacle accepts 254XXXXXXXXX format (without +)
        if (substr($phone, 0, 3) === '254') {
            // Already in international format
            return $phone;
        } elseif (substr($phone, 0, 1) === '0') {
            // Local format starting with 0
            return '254' . substr($phone, 1);
        } elseif (strlen($phone) === 9) {
            // 9 digits without country code or leading 0
            return '254' . $phone;
        }
        
        // Return as is if format is unclear
        return $phone;
    }
    
    public function validatePhoneNumber($phone)
    {
        $formatted = $this->formatPhoneNumber($phone);
        
        // Accept any valid Kenyan number: 254 followed by exactly 9 digits
        // Covers Safaricom (2547x), Airtel (2541x), Telkom (2540x) etc.
        return preg_match('/^254[0-9]{9}$/', $formatted);
    }
}
