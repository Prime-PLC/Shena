<?php
/**
 * Clean C2B validation URL for Daraja URL Management.
 *
 * The actual accounting record is created by the confirmation callback. This
 * endpoint only tells Daraja to allow the customer transaction to proceed.
 */
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/config.php';

$logDir = ROOT_PATH . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = $logDir . '/shena_validation_' . date('Y-m-d') . '.log';
$rawInput = file_get_contents('php://input');
$log = date('Y-m-d H:i:s') . " - Validation Request\n";
$log .= "Method: " . ($_SERVER['REQUEST_METHOD'] ?? '') . "\n";
$log .= "IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
$log .= "Raw Input: " . $rawInput . "\n";
$log .= str_repeat('-', 80) . "\n";
file_put_contents($logFile, $log, FILE_APPEND);

http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'ResultCode' => 0,
    'ResultDesc' => 'Accepted',
]);
