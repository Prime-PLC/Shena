<?php
/**
 * M-Pesa C2B Callback Handler
 * Receives and processes M-Pesa Paybill payment notifications
 * Endpoint: https://yourdomain.com/mpesa/c2b/callback
 */

require_once __DIR__ . '/../vendor/autoload.php'; // If using Composer
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/services/PaymentReconciliationService.php';

// Log all incoming requests
$logFile = __DIR__ . '/../storage/logs/mpesa_c2b_' . date('Y-m-d') . '.log';
$requestLog = date('Y-m-d H:i:s') . " - C2B Callback Received\n";
$requestLog .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$requestLog .= "Raw Input: " . file_get_contents('php://input') . "\n";
$requestLog .= "Headers: " . json_encode(getallheaders()) . "\n\n";
file_put_contents($logFile, $requestLog, FILE_APPEND);

// Validate caller IP against known Safaricom IP ranges
function safaricom_ip_allowed_c2b(string $ip): bool {
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
if (!safaricom_ip_allowed_c2b($callerIp)) {
    http_response_code(403);
    file_put_contents($logFile, "Error: Forbidden IP {$callerIp}\n\n", FILE_APPEND);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Forbidden']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Method Not Allowed']);
    exit;
}

// Get raw callback data
$rawInput = file_get_contents('php://input');
$callbackData = json_decode($rawInput, true);

// Log parsed data
$parsedLog = "Parsed Data: " . json_encode($callbackData, JSON_PRETTY_PRINT) . "\n\n";
file_put_contents($logFile, $parsedLog, FILE_APPEND);

// Validate callback data
if (empty($callbackData) || !isset($callbackData['TransID'])) {
    http_response_code(400);
    $error = ['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data'];
    file_put_contents($logFile, "Error: Invalid data\n\n", FILE_APPEND);
    echo json_encode($error);
    exit;
}

try {
    // Process callback using reconciliation service
    $reconciliationService = new PaymentReconciliationService();
    $result = $reconciliationService->processC2BCallback($callbackData);
    
    // Log result
    $resultLog = "Processing Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    $resultLog .= str_repeat('-', 80) . "\n\n";
    file_put_contents($logFile, $resultLog, FILE_APPEND);
    
    // Return success response to M-Pesa
    http_response_code(200);
    echo json_encode([
        'ResultCode' => 0,
        'ResultDesc' => 'Success'
    ]);
    
} catch (Exception $e) {
    // Log error
    $errorLog = "Error Processing Callback: " . $e->getMessage() . "\n";
    $errorLog .= "Stack Trace: " . $e->getTraceAsString() . "\n";
    $errorLog .= str_repeat('-', 80) . "\n\n";
    file_put_contents($logFile, $errorLog, FILE_APPEND);
    
    // Still return success to M-Pesa to prevent retries
    http_response_code(200);
    echo json_encode([
        'ResultCode' => 0,
        'ResultDesc' => 'Accepted'
    ]);
}
