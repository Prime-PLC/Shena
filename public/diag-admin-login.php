<?php
/**
 * Temporary diagnostic endpoint for admin-login 500 errors.
 * REMOVE after debugging.
 *
 * Usage:
 *   /public/diag-admin-login.php?key=shena-debug-2026&route=/admin-login
 */

if (!isset($_GET['key']) || $_GET['key'] !== 'shena-debug-2026') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '1');

register_shutdown_function(function () {
    $lastError = error_get_last();
    if ($lastError) {
        echo "\n\n=== FATAL/SHUTDOWN ERROR ===\n";
        echo 'Type: ' . ($lastError['type'] ?? 'unknown') . "\n";
        echo 'Message: ' . ($lastError['message'] ?? 'n/a') . "\n";
        echo 'File: ' . ($lastError['file'] ?? 'n/a') . "\n";
        echo 'Line: ' . ($lastError['line'] ?? 'n/a') . "\n";
    }
});

$rootPath = dirname(__DIR__);
$routeToTest = isset($_GET['route']) && $_GET['route'] !== '' ? $_GET['route'] : '/admin-login';

echo "PHP Version: " . PHP_VERSION . "\n";
echo "ROOT_PATH: " . $rootPath . "\n";
echo "ROUTE_TO_TEST: " . $routeToTest . "\n";

echo "AdminController exists: " . (file_exists($rootPath . '/app/controllers/AdminController.php') ? 'yes' : 'no') . "\n";
echo "Admin login view exists: " . (file_exists($rootPath . '/resources/views/admin/login.php') ? 'yes' : 'no') . "\n";

$adminControllerPath = $rootPath . '/app/controllers/AdminController.php';
if (file_exists($adminControllerPath)) {
    $adminControllerContent = file_get_contents($adminControllerPath);
    $adminControllerLines = @file($adminControllerPath, FILE_IGNORE_NEW_LINES);
    echo "AdminController size(bytes): " . strlen($adminControllerContent) . "\n";
    echo "AdminController md5: " . md5($adminControllerContent) . "\n";
    echo "AdminController line_count: " . (is_array($adminControllerLines) ? count($adminControllerLines) : 0) . "\n";

    if (is_array($adminControllerLines) && count($adminControllerLines) > 0) {
        echo "\n=== AdminController tail (last 12 lines) ===\n";
        $start = max(0, count($adminControllerLines) - 12);
        for ($i = $start; $i < count($adminControllerLines); $i++) {
            $lineNo = $i + 1;
            echo $lineNo . ': ' . $adminControllerLines[$i] . "\n";
        }
        echo "=== End tail ===\n";
    }
}

echo "\n=== DB DIAGNOSTICS ===\n";
try {
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', $rootPath);
    }
    require_once $rootPath . '/config/config.php';
    require_once $rootPath . '/app/core/Database.php';

    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "DB connection: OK\n";

    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    $usersTable = $stmt ? $stmt->fetch() : false;
    echo "users table exists: " . ($usersTable ? 'yes' : 'no') . "\n";

    if ($usersTable) {
        $row = $db->fetch("SELECT COUNT(*) AS c FROM users", []);
        echo "users count: " . (($row && isset($row['c'])) ? $row['c'] : 'n/a') . "\n";

        $admin = $db->fetch(
            "SELECT id, email, role, status FROM users WHERE email = :email LIMIT 1",
            ['email' => 'test.superadmin@shena.local']
        );
        if ($admin) {
            echo "test admin exists: yes\n";
            echo "test admin role: " . ($admin['role'] ?? 'n/a') . "\n";
            echo "test admin status: " . ($admin['status'] ?? 'n/a') . "\n";
        } else {
            echo "test admin exists: no\n";
        }
    }
} catch (Throwable $e) {
    echo "DB connection: FAIL\n";
    echo "DB error: " . $e->getMessage() . "\n";
}

echo "\n=== Simulating GET route ===\n";

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = $routeToTest;

ob_start();
try {
    include $rootPath . '/index.php';
} catch (Throwable $e) {
    echo "\n=== CAUGHT THROWABLE ===\n";
    echo 'Message: ' . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . "\n";
    echo 'Line: ' . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
$output = ob_get_clean();

echo "\n=== ROUTE OUTPUT (first 1500 chars) ===\n";
echo substr($output, 0, 1500) . "\n";
