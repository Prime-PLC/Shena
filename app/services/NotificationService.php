<?php
/**
 * Notification Service - Handles SMS with Email Fallback
 * Automatically sends email when SMS fails
 */

require_once __DIR__ . '/SmsService.php';
require_once __DIR__ . '/EmailService.php';

class NotificationService 
{
    private $smsService;
    private $emailService;
    private $db;
    
    public function __construct()
    {
        $this->smsService = new SmsService();
        $this->emailService = new EmailService();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Send notification with SMS first, email as fallback
     * 
     * @param array $recipient ['phone' => '', 'email' => '', 'name' => '']
     * @param string $message SMS message
     * @param string $emailSubject Email subject
     * @param string $emailBody Email body (optional, defaults to SMS message)
     * @param bool $enableFallback Whether to use email fallback (default true)
     * @return array ['success' => bool, 'method' => 'sms|email|failed', 'error' => string]
     */
    public function send($recipient, $message, $emailSubject = null, $emailBody = null, $enableFallback = true)
    {
        $result = [
            'success' => false,
            'method' => null,
            'sms_attempted' => false,
            'email_attempted' => false,
            'error' => null
        ];
        
        // Try SMS first if phone number provided
        if (!empty($recipient['phone'])) {
            $result['sms_attempted'] = true;
            $smsResult = $this->smsService->sendSms($recipient['phone'], $message);
            
            if ($smsResult['success']) {
                $result['success'] = true;
                $result['method'] = 'sms';
                $result['data'] = $smsResult['data'] ?? null;
                $this->logNotification($recipient, 'sms', 'success', $message);
                return $result;
            } else {
                $result['sms_error'] = $smsResult['error'] ?? 'Unknown SMS error';
                error_log('SMS failed for ' . $recipient['phone'] . ': ' . $result['sms_error']);
            }
        }
        
        // Fallback to email if SMS failed and fallback is enabled
        if ($enableFallback && !empty($recipient['email'])) {
            $result['email_attempted'] = true;
            
            // Use provided email body or format SMS message as email
            $finalEmailBody = $emailBody ?? $this->formatSmsAsEmail($message, $recipient['name'] ?? 'Member');
            $finalSubject = $emailSubject ?? 'Message from Shena Companion';
            
            $emailResult = $this->emailService->sendEmail(
                $recipient['email'],
                $finalSubject,
                $finalEmailBody,
                true
            );
            
            if ($emailResult) {
                $result['success'] = true;
                $result['method'] = 'email';
                $result['fallback_used'] = true;
                $result['data'] = ['fallback' => 'email'];
                $this->logNotification($recipient, 'email', 'success', $message, 'SMS failed, email fallback used');
                return $result;
            } else {
                $result['email_error'] = 'Email delivery failed';
                error_log('Email fallback also failed for ' . $recipient['email']);
            }
        }
        
        // Both methods failed
        $result['error'] = 'All notification methods failed';
        if (isset($result['sms_error'])) {
            $result['error'] .= ' - SMS: ' . $result['sms_error'];
        }
        if (isset($result['email_error'])) {
            $result['error'] .= ' - Email: ' . $result['email_error'];
        }
        
        $this->logNotification($recipient, 'failed', 'failed', $message, $result['error']);
        
        return $result;
    }
    
    /**
     * Format SMS message as HTML email
     */
    private function formatSmsAsEmail($message, $recipientName)
    {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2c5282; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f7fafc; padding: 30px; border: 1px solid #e2e8f0; }
                .message { background-color: white; padding: 20px; border-left: 4px solid #2c5282; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #718096; font-size: 12px; }
                .note { background-color: #fef5e7; border: 1px solid #f39c12; padding: 15px; margin: 20px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Shena Companion Welfare Association</h2>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($recipientName) . ",</p>
                    
                    <div class='note'>
                        <strong>📧 Email Delivery Notice:</strong><br>
                        We attempted to send you an SMS but were unable to deliver it. This message has been sent to your email instead.
                    </div>
                    
                    <div class='message'>
                        <strong>Message:</strong><br>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                    
                    <p>If you have any questions, please contact us or visit your member portal.</p>
                    
                    <p>Best regards,<br>
                    <strong>Shena Companion Team</strong></p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Shena Companion Welfare Association. All rights reserved.</p>
                    <p>This email was sent as a fallback because SMS delivery was not possible.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Log notification attempt to database
     */
    private function logNotification($recipient, $method, $status, $message, $notes = null)
    {
        try {
            $sql = "INSERT INTO notification_logs 
                    (phone, email, recipient_name, method, status, message, notes, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $recipient['phone'] ?? null,
                $recipient['email'] ?? null,
                $recipient['name'] ?? null,
                $method,
                $status,
                substr($message, 0, 500), // Truncate long messages
                $notes
            ]);
        } catch (Exception $e) {
            error_log('Failed to log notification: ' . $e->getMessage());
        }
    }
    
    /**
     * Send bulk notifications with automatic fallback
     */
    public function sendBulk($recipients, $message, $emailSubject = null, $emailBody = null, $enableFallback = true)
    {
        $results = [
            'total' => count($recipients),
            'sent_sms' => 0,
            'sent_email' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        foreach ($recipients as $recipient) {
            $result = $this->send($recipient, $message, $emailSubject, $emailBody, $enableFallback);
            
            if ($result['success']) {
                if ($result['method'] === 'sms') {
                    $results['sent_sms']++;
                } else {
                    $results['sent_email']++;
                }
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = [
                'recipient' => $recipient['phone'] ?? $recipient['email'],
                'result' => $result
            ];
            
            // Small delay to avoid rate limiting
            usleep(100000); // 100ms
        }
        
        return $results;
    }
    
    /**
     * Get notification statistics
     */
    public function getStats($dateFrom = null, $dateTo = null)
    {
        try {
            $sql = "SELECT 
                        method,
                        status,
                        COUNT(*) as count
                    FROM notification_logs
                    WHERE 1=1";
            
            $params = [];
            
            if ($dateFrom) {
                $sql .= " AND created_at >= ?";
                $params[] = $dateFrom;
            }
            
            if ($dateTo) {
                $sql .= " AND created_at <= ?";
                $params[] = $dateTo;
            }
            
            $sql .= " GROUP BY method, status";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log('Failed to get notification stats: ' . $e->getMessage());
            return [];
        }
    }
}
