<?php
/**
 * Production Diagnostic Script
 * IMPORTANT: Delete or restrict access to this file after diagnosing the issue.
 * Access: https://shenacompanion.co.ke/public/diag-check.php
 */

// Only allow localhost or if a secret key is provided
$secret = $_GET['key'] ?? '';
$validKey = 'shena-diag-2026'; // Change this after use
if ($secret !== $validKey && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die('Access denied. Append ?key=shena-diag-2026 to the URL.');
}

header('Content-Type: text/plain; charset=utf-8');

define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');

echo "=== SHENA Diagnostic Report ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "SAPI: " . PHP_SAPI . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n\n";

// Check .env file
echo "--- .env file ---\n";
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    echo "EXISTS\n";
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        // Mask sensitive values
        if (preg_match('/^(DB_PASS|MAIL_PASSWORD|MPESA_|HOSTPINNACLE)/i', $line)) {
            $parts = explode('=', $line, 2);
            echo $parts[0] . "=***MASKED***\n";
        } else {
            echo $line . "\n";
        }
    }
} else {
    echo "NOT FOUND - falls back to defaults in config.php\n";
}

echo "\n--- Config Load ---\n";
try {
    require_once CONFIG_PATH . '/config.php';
    echo "config.php loaded OK\n";
    echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "\n";
    echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "\n";
    echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "\n";
    echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "\n";
    echo "DB_PASS: " . (defined('DB_PASS') ? (empty(DB_PASS) ? '(empty)' : '***SET***') : 'NOT DEFINED') . "\n";
    echo "DEBUG_MODE: " . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'true' : 'false') : 'NOT DEFINED') . "\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}

echo "\n--- Database Connection ---\n";
try {
    require_once ROOT_PATH . '/app/core/Database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "Connected OK\n";
    $stmt = $conn->query("SELECT VERSION() as v");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "MySQL version: " . $row['v'] . "\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}

echo "\n--- Key Tables ---\n";
try {
    $tables = ['users', 'members', 'payments', 'claims', 'communications', 'communication_recipients', 'bulk_messages', 'settings'];
    foreach ($tables as $table) {
        try {
            $conn->query("SELECT 1 FROM {$table} LIMIT 1");
            echo "$table: EXISTS\n";
        } catch (Throwable $e) {
            echo "$table: MISSING ({$e->getMessage()})\n";
        }
    }
} catch (Throwable $e) {
    echo "Table check failed: " . $e->getMessage() . "\n";
}

echo "\n--- recent error.log (last 20 lines) ---\n";
$logFile = ROOT_PATH . '/storage/logs/error.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $last = array_slice($lines, -20);
    echo implode('', $last);
} else {
    echo "No error.log found at: $logFile\n";
}

echo "\n=== End Diagnostic ===\n";
