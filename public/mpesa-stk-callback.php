<?php
/**
 * M-Pesa STK Push Callback Handler
 * Receives and processes M-Pesa STK Push payment responses
 * Endpoint: https://yourdomain.com/public/mpesa-stk-callback.php
 */

// Define ROOT_PATH
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/app/core/Database.php';
require_once ROOT_PATH . '/app/core/BaseModel.php';
require_once ROOT_PATH . '/app/models/Payment.php';
require_once ROOT_PATH . '/app/models/Member.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/services/EmailService.php';
require_once ROOT_PATH . '/app/services/SmsService.php';

// Create logs directory if it doesn't exist
$logDir = ROOT_PATH . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Log all incoming requests
$logFile = $logDir . '/mpesa_stk_' . date('Y-m-d') . '.log';
$requestLog = str_repeat('=', 80) . "\n";
$requestLog .= date('Y-m-d H:i:s') . " - STK Push Callback Received\n";
$requestLog .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$requestLog .= "IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
$requestLog .= "Raw Input: " . file_get_contents('php://input') . "\n";
$requestLog .= "Headers: " . json_encode(getallheaders()) . "\n";
$requestLog .= str_repeat('-', 80) . "\n";
file_put_contents($logFile, $requestLog, FILE_APPEND);

// Validate caller IP against known Safaricom IP ranges
// Production: 196.201.214.0/24, 196.201.215.0/24
// Sandbox:    196.201.214.200
function safaricom_ip_allowed(string $ip): bool {
    // Allow localhost / private ranges during local development only
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        return true;
    }
    $allowedCidrs = [
        '196.201.214.0/24',
        '196.201.215.0/24',
    ];
    $long = ip2long($ip);
    if ($long === false) return false;
    foreach ($allowedCidrs as $cidr) {
        [$base, $bits] = explode('/', $cidr);
        $mask = -1 << (32 - (int)$bits);
        if ((ip2long($base) & $mask) === ($long & $mask)) return true;
    }
    return false;
}

$callerIp = $_SERVER['REMOTE_ADDR'] ?? '';
if (!safaricom_ip_allowed($callerIp)) {
    http_response_code(403);
    file_put_contents($logFile, "Error: Forbidden IP {$callerIp}\n\n", FILE_APPEND);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Forbidden']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $error = ['ResultCode' => 1, 'ResultDesc' => 'Method Not Allowed'];
    file_put_contents($logFile, "Error: Method Not Allowed\n\n", FILE_APPEND);
    echo json_encode($error);
    exit;
}

// Get raw callback data
$rawInput = file_get_contents('php://input');
$callbackData = json_decode($rawInput, true);

// Log parsed data
$parsedLog = "Parsed Data: " . json_encode($callbackData, JSON_PRETTY_PRINT) . "\n";
file_put_contents($logFile, $parsedLog, FILE_APPEND);

// Validate callback data structure
if (empty($callbackData) || !isset($callbackData['Body'])) {
    http_response_code(400);
    $error = ['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data structure'];
    file_put_contents($logFile, "Error: Invalid data structure\n\n", FILE_APPEND);
    echo json_encode($error);
    exit;
}

try {
    $db = Database::getInstance();
    $paymentModel = new Payment();
    $memberModel = new Member();
    
    // Extract callback data
    $body = $callbackData['Body'];
    $stkCallback = $body['stkCallback'];
    
    $merchantRequestId = $stkCallback['MerchantRequestID'] ?? '';
    $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? '';
    $resultCode = $stkCallback['ResultCode'] ?? '';
    $resultDesc = $stkCallback['ResultDesc'] ?? '';
    
    $processLog = "Processing STK Callback:\n";
    $processLog .= "  MerchantRequestID: {$merchantRequestId}\n";
    $processLog .= "  CheckoutRequestID: {$checkoutRequestId}\n";
    $processLog .= "  ResultCode: {$resultCode}\n";
    $processLog .= "  ResultDesc: {$resultDesc}\n";
    
    // ResultCode 0 means success
    if ($resultCode == 0) {
        $processLog .= "  Status: SUCCESSFUL\n";
        
        // Extract payment metadata
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
                    // Format: 20230615120000 -> 2023-06-15 12:00:00
                    $dateStr = (string)$item['Value'];
                    $transactionDate = date('Y-m-d H:i:s', strtotime(
                        substr($dateStr, 0, 4) . '-' . 
                        substr($dateStr, 4, 2) . '-' . 
                        substr($dateStr, 6, 2) . ' ' .
                        substr($dateStr, 8, 2) . ':' . 
                        substr($dateStr, 10, 2) . ':' . 
                        substr($dateStr, 12, 2)
                    ));
                    break;
            }
        }
        
        $processLog .= "  Amount: KES {$amount}\n";
        $processLog .= "  Receipt: {$mpesaReceiptNumber}\n";
        $processLog .= "  Phone: {$phoneNumber}\n";
        $processLog .= "  Date: {$transactionDate}\n";
        
        // Find the payment record by checkout request ID
        $payment = $db->fetch(
            "SELECT * FROM payments WHERE transaction_reference = :checkout_id",
            ['checkout_id' => $checkoutRequestId]
        );
        
        if ($payment) {
            $processLog .= "  Payment Record Found: ID {$payment['id']}\n";
            
            $paymentModel->confirmPayment($payment['id'], $mpesaReceiptNumber);
            $updated = $db->execute(
                "UPDATE payments SET
                    transaction_date = :trans_date,
                    payment_date = :trans_date,
                    sender_phone = :phone,
                    reconciliation_status = 'matched',
                    auto_matched = 1,
                    reconciled_at = NOW()
                 WHERE id = :id",
                [
                    'trans_date' => $transactionDate,
                    'phone' => $phoneNumber,
                    'id' => $payment['id']
                ]
            );
            
            if ($updated) {
                $processLog .= "  Payment Updated Successfully\n";
                
                // Handle registration fee activation
                if ($payment['payment_type'] === 'registration') {
                    $member = $memberModel->find($payment['member_id']);
                    if ($member && $member['status'] === 'inactive') {
                        // Check total registration payments
                        $totalPaid = $db->fetchColumn(
                            "SELECT COALESCE(SUM(amount), 0) FROM payments 
                            WHERE member_id = :member_id 
                            AND payment_type = 'registration' 
                            AND status = 'completed'",
                            ['member_id' => $payment['member_id']]
                        );
                        
                        if ($totalPaid >= REGISTRATION_FEE) {
                            $memberModel->update($payment['member_id'], [
                                'status' => 'active',
                                'coverage_ends' => date('Y-m-d', strtotime('+1 year'))
                            ]);
                            
                            $userModel = new User();
                            $userModel->update($member['user_id'], ['status' => 'active']);
                            
                            $processLog .= "  Member Activated: ID {$payment['member_id']}\n";
                        }
                    }
                }
                
                // Handle reactivation
                if ($payment['payment_type'] === 'reactivation') {
                    $member = $memberModel->find($payment['member_id']);
                    if ($member && $member['status'] === 'defaulted') {
                        $memberModel->reactivateMember($payment['member_id']);
                        $processLog .= "  Member Reactivated: ID {$payment['member_id']}\n";
                    }
                }

                // Handle monthly contribution — extend coverage and clear grace period
                if ($payment['payment_type'] === 'monthly') {
                    $memberModel->applySuccessfulMonthlyPayment(
                        $payment['member_id'],
                        $transactionDate ?: date('Y-m-d H:i:s')
                    );
                    $processLog .= "  Monthly coverage extended: Member ID {$payment['member_id']}\n";
                }
                
                // Send notifications
                try {
                    $memberData = $memberModel->getMemberWithUser($payment['member_id']);
                    if ($memberData) {
                        // Send SMS
                        $smsService = new SmsService();
                        $smsService->sendPaymentConfirmationSms($memberData['phone'], [
                            'amount' => $amount,
                            'transaction_id' => $mpesaReceiptNumber
                        ]);
                        
                        // Send Email
                        $emailService = new EmailService();
                        $emailService->sendPaymentConfirmationEmail($memberData['email'], [
                            'name' => $memberData['first_name'] . ' ' . $memberData['last_name'],
                            'amount' => $amount,
                            'transaction_id' => $mpesaReceiptNumber,
                            'payment_date' => $transactionDate
                        ]);
                        
                        $processLog .= "  Notifications Sent\n";
                    }
                } catch (Exception $e) {
                    $processLog .= "  Notification Error: " . $e->getMessage() . "\n";
                }
            }
        } else {
            $processLog .= "  WARNING: Payment record not found for CheckoutRequestID\n";
        }
        
    } else {
        // Payment failed or cancelled
        $processLog .= "  Status: FAILED/CANCELLED\n";
        
        // Find and update payment record
        $payment = $db->fetch(
            "SELECT * FROM payments WHERE transaction_reference = :checkout_id",
            ['checkout_id' => $checkoutRequestId]
        );
        
        if ($payment) {
            $db->execute(
                "UPDATE payments SET 
                    status = 'failed',
                    notes = :notes
                WHERE id = :id",
                [
                    'notes' => "Failed: {$resultDesc}",
                    'id' => $payment['id']
                ]
            );
            $processLog .= "  Payment marked as failed: ID {$payment['id']}\n";
        }
    }
    
    file_put_contents($logFile, $processLog . "\n", FILE_APPEND);
    
    // Return success response to M-Pesa
    http_response_code(200);
    echo json_encode([
        'ResultCode' => 0,
        'ResultDesc' => 'Success'
    ]);
    
} catch (Exception $e) {
    // Log error
    $errorLog = "ERROR Processing STK Callback:\n";
    $errorLog .= "  Message: " . $e->getMessage() . "\n";
    $errorLog .= "  File: " . $e->getFile() . "\n";
    $errorLog .= "  Line: " . $e->getLine() . "\n";
    $errorLog .= "  Stack Trace:\n" . $e->getTraceAsString() . "\n";
    $errorLog .= str_repeat('=', 80) . "\n\n";
    file_put_contents($logFile, $errorLog, FILE_APPEND);
    
    // Still return success to M-Pesa to prevent retries
    http_response_code(200);
    echo json_encode([
        'ResultCode' => 0,
        'ResultDesc' => 'Accepted'
    ]);
}
