<?php
/**
 * Email Service - Handles email sending via PHPMailer / SMTP
 */

// Load Composer autoloader for PHPMailer namespaced classes
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

// Explicit requires so static analysis tools can resolve PHPMailer classes
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

require_once __DIR__ . '/../core/Database.php';

class EmailService 
{
    private $config;
    
    public function __construct()
    {
        $this->config = [
            'host' => MAIL_HOST,
            'port' => MAIL_PORT,
            'username' => MAIL_USERNAME,
            'password' => MAIL_PASSWORD,
            'from_email' => MAIL_FROM_EMAIL,
            'from_name' => MAIL_FROM_NAME
        ];
    }
    
    public function sendEmail($to, $subject, $body, $isHtml = true)
    {
        try {
            // Basic validation: if recipient missing, skip sending
            if (empty($to)) {
                error_log('Email not sent: recipient email is empty');
                $this->logEmailAttempt($to, $subject, $body, 'skipped', 'empty_recipient');
                return false;
            }

            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config['username'];
            $mail->Password   = $this->config['password'];
            $mail->SMTPSecure = (intval($this->config['port']) === 465)
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = intval($this->config['port']);
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Sender & recipient
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addReplyTo($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            if ($isHtml) {
                $mail->Body    = $body;
                $mail->AltBody = strip_tags($body);
            } else {
                $mail->Body = $body;
            }

            $mail->send();

            $this->logEmailAttempt($to, $subject, $body, 'sent');
            return true;

        } catch (PHPMailerException $e) {
            error_log('Email sending failed (PHPMailer): ' . $e->getMessage());
            $this->logEmailAttempt($to, $subject, $body, 'failed', $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log('Email sending failed: ' . $e->getMessage());
            $this->logEmailAttempt($to, $subject, $body, 'failed', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log email attempt to database
     */
    private function logEmailAttempt($to, $subject, $body, $status, $error = null)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO notification_logs 
                (notification_type, recipient, recipient_type, subject, message, status, error_message, sent_at)
                VALUES ('email', ?, 'email', ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$to, $subject, $body, $status, $error]);
        } catch (Exception $e) {
            error_log('Failed to log email attempt: ' . $e->getMessage());
        }
    }
    
    public function sendWelcomeEmail($email, $data)
    {
        $subject = 'Welcome to Shena Companion Welfare Association';
        
        $body = $this->getEmailTemplate('welcome', [
            'name' => $data['name'],
            'member_number' => $data['member_number']
        ]);
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    public function sendAccountActivationEmail($email, $data)
    {
        $subject = 'Your Shena Companion Account Has Been Activated';
        
        $body = $this->getEmailTemplate('activation', [
            'name' => $data['name'],
            'member_number' => $data['member_number']
        ]);
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    public function sendPaymentReminderEmail($email, $data)
    {
        $subject = 'Monthly Contribution Payment Reminder';
        
        $body = $this->getEmailTemplate('payment_reminder', [
            'name' => $data['name'],
            'member_number' => $data['member_number'],
            'amount' => $data['amount'],
            'due_date' => $data['due_date']
        ]);
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    public function sendPaymentConfirmationEmail($email, $data)
    {
        $subject = 'Payment Confirmation - Shena Companion';
        
        $body = $this->getEmailTemplate('payment_confirmation', [
            'name' => $data['name'],
            'amount' => $data['amount'],
            'transaction_id' => $data['transaction_id'],
            'payment_date' => $data['payment_date']
        ]);
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    public function sendClaimNotificationEmail($member, $claimData)
    {
        $adminEmail = 'admin@shenacompanion.org'; // Configure this
        $subject = 'New Claim Submitted - Member: ' . $member['member_number'];
        
        $body = $this->getEmailTemplate('claim_notification', [
            'member_name' => $member['first_name'] . ' ' . $member['last_name'],
            'member_number' => $member['member_number'],
            'deceased_name' => $claimData['deceased_name'],
            'claim_amount' => $claimData['claim_amount'],
            'date_of_death' => $claimData['date_of_death']
        ]);
        
        return $this->sendEmail($adminEmail, $subject, $body);
    }
    
    public function sendClaimStatusUpdateEmail($email, $data)
    {
        $subject = 'Claim Status Update - ' . ucfirst($data['status']);
        
        $body = $this->getEmailTemplate('claim_status', [
            'name' => $data['name'],
            'status' => $data['status'],
            'claim_id' => $data['claim_id'],
            'notes' => $data['notes'] ?? '',
            'approved_amount' => $data['approved_amount'] ?? null
        ]);
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    public function sendContactFormEmail($data)
    {
        $adminEmail = 'admin@shenacompanion.org'; // Configure this
        $subject = 'Contact Form Submission: ' . $data['subject'];
        
        $body = $this->getEmailTemplate('contact_form', $data);
        
        return $this->sendEmail($adminEmail, $subject, $body);
    }
    
    public function sendBulkEmail($recipients, $subject, $body)
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $email = is_array($recipient) ? $recipient['email'] : $recipient;
            $results[] = $this->sendEmail($email, $subject, $body);
            
            // Add small delay to avoid overwhelming the mail server
            usleep(100000); // 0.1 seconds
        }
        
        return $results;
    }
    
    private function getEmailTemplate($template, $data = [])
    {
        $templatePath = VIEWS_PATH . '/emails/' . $template . '.php';
        
        if (file_exists($templatePath)) {
            ob_start();
            extract($data);
            include $templatePath;
            return ob_get_clean();
        }
        
        // Fallback to simple text template
        return $this->getSimpleTemplate($template, $data);
    }
    
    private function getSimpleTemplate($template, $data)
    {
        switch ($template) {
            case 'welcome':
                return "
                <html>
                <body>
                    <h2>Welcome to Shena Companion Welfare Association</h2>
                    <p>Dear {$data['name']},</p>
                    <p>Thank you for registering with Shena Companion Welfare Association.</p>
                    <p>Your member number is: <strong>{$data['member_number']}</strong></p>
                    <p>Your membership application is currently under review. We will notify you once it's approved.</p>
                    <br>
                    <p>Best regards,<br>Shena Companion Welfare Association</p>
                </body>
                </html>";
                
            case 'payment_reminder':
                return "
                <html>
                <body>
                    <h2>Payment Reminder</h2>
                    <p>Dear {$data['name']},</p>
                    <p>This is a reminder that your monthly contribution of KES {$data['amount']} is due.</p>
                    <p>Member Number: {$data['member_number']}</p>
                    <p>Please make your payment through M-Pesa Paybill: 4163987</p>
                    <br>
                    <p>Best regards,<br>Shena Companion Welfare Association</p>
                </body>
                </html>";
                
            default:
                return "
                <html>
                <body>
                    <h2>Shena Companion Welfare Association</h2>
                    <p>Thank you for your interest in our services.</p>
                    <br>
                    <p>Best regards,<br>Shena Companion Welfare Association</p>
                </body>
                </html>";
        }
    }
    
    /**
     * Send grace period warning email
     */
    public function sendGracePeriodWarning($email, $data)
    {
        $subject = 'URGENT: Account Entering Grace Period - Payment Required';
        
        $body = "
        <html>
        <head>
            <style>
                .warning { background-color: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; }
                .btn { background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; display: inline-block; }
            </style>
        </head>
        <body>
            <h2>Shena Companion Welfare Association</h2>
            <div class='warning'>
                <h3>⚠️ Grace Period Warning</h3>
                <p>Dear {$data['name']},</p>
                <p>Your account (Member #: {$data['member_number']}) has entered the <strong>grace period</strong> due to missed contributions.</p>
                
                <p><strong>Important Information:</strong></p>
                <ul>
                    <li><strong>Days Remaining:</strong> {$data['days_left']} days</li>
                    <li><strong>Grace Period Expires:</strong> {$data['expiry_date']}</li>
                    <li><strong>Amount Due:</strong> KES " . number_format($data['amount']) . "</li>
                </ul>
                
                <p><strong>What happens if payment is not made:</strong></p>
                <ul>
                    <li>Your account will be suspended</li>
                    <li>Coverage will be terminated</li>
                    <li>Reactivation requires additional fees</li>
                </ul>
                
                <p style='margin-top: 20px;'>
                    <a href='" . APP_URL . "/payments' class='btn'>Make Payment Now</a>
                </p>
                
                <p><strong>Payment Instructions:</strong><br>
                M-Pesa Paybill: <strong>" . MPESA_BUSINESS_SHORTCODE . "</strong><br>
                Account Number: Your ID Number<br>
                Amount: KES " . number_format($data['amount']) . "</p>
            </div>
            
            <p>For assistance, contact us:<br>
            Phone: 0748585067 / 0748585071<br>
            Email: info@shenacompanion.org</p>
            
            <p>Best regards,<br>Shena Companion Welfare Association</p>
        </body>
        </html>";
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    /**
     * Send account suspended notification
     */
    public function sendAccountSuspendedEmail($email, $data)
    {
        $subject = 'Account Suspended - Immediate Action Required';
        
        $body = "
        <html>
        <head>
            <style>
                .danger { background-color: #f8d7da; padding: 20px; border-left: 4px solid #dc3545; }
            </style>
        </head>
        <body>
            <h2>Shena Companion Welfare Association</h2>
            <div class='danger'>
                <h3>🚫 Account Suspended</h3>
                <p>Dear {$data['name']},</p>
                <p>Your account (Member #: {$data['member_number']}) has been <strong>suspended</strong> due to non-payment exceeding the grace period.</p>
                
                <p><strong>Reactivation Requirements:</strong></p>
                <ol>
                    <li>Pay all outstanding contributions: KES " . number_format($data['outstanding_amount']) . "</li>
                    <li>Pay reactivation fee: KES " . number_format(REACTIVATION_FEE) . "</li>
                    <li>Complete new 4-month maturity period</li>
                </ol>
                
                <p><strong>Total Amount Due:</strong> KES " . number_format($data['total_due']) . "</p>
                
                <p>To reactivate your account, please contact our office or make payment via M-Pesa.</p>
            </div>
            
            <p>Contact Us:<br>
            Phone: 0748585067 / 0748585071<br>
            Email: info@shenacompanion.org</p>
            
            <p>Best regards,<br>Shena Companion Welfare Association</p>
        </body>
        </html>";
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    /**
     * Send registration confirmation email
     */
    public function sendRegistrationConfirmation($email, $data)
    {
        $subject = 'Registration Successful - Shena Companion';
        
        // Extract data with defaults to prevent undefined key warnings
        $name = $data['name'] ?? 'Member';
        $memberNumber = $data['member_number'] ?? 'N/A';
        $packageName = $data['package_name'] ?? $data['package'] ?? 'N/A';
        $monthlyContribution = $data['monthly_contribution'] ?? $data['monthly_amount'] ?? 0;
        $maturityDate = $data['maturity_date'] ?? 'N/A';
        $maturityMonths = $data['maturity_months'] ?? 4;
        
        $body = "
        <html>
        <body>
            <h2>Shena Companion Welfare Association</h2>
            <h3>✅ Registration Successful!</h3>
            <p>Dear {$name},</p>
            <p>Thank you for registering with Shena Companion Welfare Association.</p>
            
            <p><strong>Your Membership Details:</strong></p>
            <ul>
                <li><strong>Member Number:</strong> {$memberNumber}</li>
                <li><strong>Package:</strong> {$packageName}</li>
                <li><strong>Monthly Contribution:</strong> KES " . number_format($monthlyContribution) . "</li>
                <li><strong>Maturity Date:</strong> {$maturityDate}</li>
            </ul>
            
            <p><strong>Important:</strong> Your coverage will become active after {$maturityMonths} months (maturity period) of consistent contributions.</p>
            
            <p><strong>First Payment:</strong><br>
            Please make your first monthly contribution payment.<br>
            M-Pesa Paybill: <strong>" . MPESA_BUSINESS_SHORTCODE . "</strong><br>
            Account Number: Your ID Number<br>
            Amount: KES " . number_format($monthlyContribution) . "</p>
            
            <p>Welcome to our family!</p>
            <p>Best regards,<br>Shena Companion Welfare Association</p>
        </body>
        </html>";
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    /**
     * Send claim approval email to member
     *
     * @param array $member Member details
     * @param array $claim Claim details
     * @return bool Success status
     */
    public function sendClaimApprovalEmail($member, $claim)
    {
        $subject = 'Claim Approved - Shena Companion Welfare Association';

        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .claim-details { background: white; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Claim Approved</h1>
                </div>
                <div class='content'>
                    <h2>Dear {$member['first_name']} {$member['last_name']},</h2>
                    <p>Your claim has been <strong>approved</strong> and is now being processed.</p>

                    <div class='claim-details'>
                        <strong>Claim Details:</strong><br>
                        Claim ID: <strong>{$claim['id']}</strong><br>
                        Member Number: <strong>{$member['member_number']}</strong><br>
                        Deceased: {$claim['deceased_name']}<br>
                        Date of Death: {$claim['date_of_death']}<br>
                        Settlement Type: " . ucfirst($claim['settlement_type'] ?? 'services') . "<br>";

        if (($claim['settlement_type'] ?? 'services') === 'cash') {
            $body .= "Cash Amount: KES " . number_format($claim['cash_settlement_amount'] ?? 20000) . "<br>";
        }

        $body .= "
                    </div>

                    <p><strong>Next Steps:</strong></p>
                    <ul>
                        <li>Our team will contact you shortly to arrange service delivery</li>
                        <li>Please ensure all required documents are available</li>
                        <li>Keep your phone accessible for coordination</li>
                    </ul>

                    <p>If you have any questions, please contact us at " . ADMIN_EMAIL . " or call " . ADMIN_PHONE . ".</p>

                    <p>Best regards,<br>Shena Companion Welfare Association</p>
                </div>
                <div class='footer'>
                    <p>Shena Companion Welfare Association<br>
                    Phone: " . ADMIN_PHONE . "</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->sendEmail($member['email'], $subject, $body);
    }

    /**
     * Send welcome email to new agent
     *
     * @param array $agent Agent details
     * @param string $password Temporary password
     * @return bool Success status
     */
    public function sendAgentWelcomeEmail($agent, $password)
    {
        $subject = 'Welcome to Shena Welfare - Agent Account Created';

        $body = "<!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .credentials { background: white; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome Agent {$agent['first_name']}!</h1>
                </div>
                <div class='content'>
                    <h2>Your Agent Account is Ready</h2>
                    <p>Your agent account has been successfully created for Shena Companion Welfare Association.</p>

                    <div class='credentials'>
                        <strong>Your Agent Details:</strong><br>
                        Agent Number: <strong>{$agent['agent_number']}</strong><br>
                        Name: {$agent['first_name']} {$agent['last_name']}<br>
                        Commission Rate: {$agent['commission_rate']}%<br><br>

                        <strong>Login Credentials:</strong><br>
                        Email: <strong>{$agent['email']}</strong><br>
                        Password: <strong>{$password}</strong><br>
                        <em style='color: #dc3545;'>Please change your password after first login</em>
                    </div>

                    <h3>Getting Started:</h3>
                    <ol>
                        <li>Log in to your agent portal</li>
                        <li>Complete your profile information</li>
                        <li>Start registering members</li>
                        <li>Track your commissions</li>
                    </ol>

                    <p><strong>Commission Structure:</strong><br>
                    You will earn {$agent['commission_rate']}% commission on all member registrations you facilitate.
                    Commissions are calculated on the first payment and approved monthly.</p>

                    <p>If you have any questions, please contact us at " . ADMIN_EMAIL . "</p>
                </div>
                <div class='footer'>
                    <p>Shena Companion Welfare Association<br>
                    Phone: " . ADMIN_PHONE . "</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->sendEmail($agent['email'], $subject, $body);
    }
}
