<?php
/**
 * SMS Service - Handles SMS sending via HostPinnacle
 */
class SmsService 
{
    private $config;
    private $apiUrl;
    
    public function __construct()
    {
        $this->config = [
            'user_id' => HOSTPINNACLE_USER_ID,
            'api_key' => HOSTPINNACLE_API_KEY,
            'sender_id' => HOSTPINNACLE_SENDER_ID
        ];
        $this->apiUrl = defined('HOSTPINNACLE_SMS_API_URL') ? HOSTPINNACLE_SMS_API_URL : 'https://smsportal.hostpinnacle.co.ke/SMSApi/send';
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
            
            $postData = http_build_query($data);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // curl_close() removed — resources are freed automatically in PHP 8+
            
            if ($httpCode == 200) {
                $result = json_decode($response, true);
                
                // HostPinnacle returns {"status":"success","transactionId":"..."} on success
                // (older docs listed status:"200" — accept both for safety)
                $statusVal = strtolower((string)($result['status'] ?? ''));
                if ($statusVal === 'success' || $statusVal === '200' || !empty($result['transactionId'])) {
                    return ['success' => true, 'data' => $result];
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
            return ['success' => false, 'error' => $e->getMessage()];
        }
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
        
        // Kenyan phone numbers should be 254 followed by 9 digits
        return preg_match('/^254[17][0-9]{8}$/', $formatted);
    }
}
