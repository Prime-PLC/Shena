<?php
/**
 * Sign in as an admin, then open /public/payment-na-diagnostic.php.
 * Optional: ?member_number=SH-123456 (includes a resolved row for comparison).
 * Default: current defaulted members whose Last Payment is N/A.
 * CSV only; SELECT queries inside a read-only transaction; no SMS or updates.
 */
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
require_once ROOT_PATH . '/config/config.php';
ini_set('display_errors', '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_secure', defined('LOCAL_OVERRIDE_APPLIED') ? '0' : '1');
ini_set('session.cookie_samesite', 'Strict');
session_start();
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit('Use GET.');
}
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['super_admin', 'manager'], true)) {
    http_response_code(403);
    exit('Sign in with an administrator account first.');
}
$userId = (int)$_SESSION['user_id'];
session_write_close();
$memberNumber = $_GET['member_number'] ?? '';
if (!is_string($memberNumber) || strlen($memberNumber) > 64) {
    http_response_code(400);
    exit('Invalid member number.');
}
$memberNumber = trim($memberNumber);
spl_autoload_register(static function ($class) {
    foreach (['core', 'models', 'services', 'helpers'] as $directory) {
        $path = APP_PATH . '/' . $directory . '/' . $class . '.php';
        if (is_file($path)) { require_once $path; return; }
    }
});
$pdo = null;
$output = null;
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $pdo->exec('SET TRANSACTION READ ONLY');
    $pdo->beginTransaction();
    $user = $db->fetch('SELECT role, status FROM users WHERE id = :id', ['id' => $userId]);
    if (!$user || $user['status'] !== 'active' || !in_array($user['role'], ['super_admin', 'manager'], true)) {
        $pdo->rollBack();
        http_response_code(403);
        exit('An active administrator account is required.');
    }
    $asOf = new DateTime('today');
    $generated = date('Y-m-d H:i:s');
    $service = new PaymentStatusService();
    // Buffer until successful completion so query failures never look like a
    // complete CSV. php://temp spills to a temporary file for large reports.
    $output = fopen('php://temp/maxmemory:5242880', 'w+');
    if ($output === false) throw new RuntimeException('Cannot prepare export');
    fputcsv($output, ['Generated at', 'Snapshot month', 'Member ID', 'Member number', 'Name',
        'Phone', 'Payment group', 'Monthly contribution', 'Paid toward current month',
        'Balance', 'Missed months', 'Last qualifying payment', 'Explanation',
        'Linked payments', 'Not completed', 'Completed excluded types',
        'Completed contributions missing date', 'Qualifying contributions',
        'Completed Platinum payments excluded', 'Qualifying transactions dated this month',
        'Qualifying records with invalid dates', 'Payment type counts', 'Payment status counts'], ',', '"', '');
    $lastId = 0;
    $exported = 0;
    do {
        $params = ['after_id' => $lastId];
        $where = "m.status = 'active' AND COALESCE(m.monthly_contribution, 0) > 0 AND m.id > :after_id";
        if ($memberNumber !== '') {
            $where .= ' AND m.member_number = :member_number';
            $params['member_number'] = $memberNumber;
        }
        $members = $db->fetchAll("SELECT m.*, u.first_name, u.last_name, u.phone
            FROM members m JOIN users u ON u.id = m.user_id
            WHERE {$where} ORDER BY m.id LIMIT 200", $params);
        foreach ($members as $member) {
            $lastId = (int)$member['id'];
            $payments = $db->fetchAll('SELECT * FROM payments WHERE member_id = :member_id
                ORDER BY COALESCE(payment_date, created_at) DESC', ['member_id' => $lastId]);
            $snapshot = $service->buildMonthlyPaymentSnapshot($member, $payments, $asOf);
            if ($memberNumber === '' && ($snapshot['payment_group'] !== 'defaulted' || $snapshot['last_payment_date'] !== null)) continue;
            $detail = PaymentNaDiagnostic::inspect($payments, $asOf->format('Y-m'));
            $row = [$generated, $asOf->format('Y-m'), $lastId, $member['member_number'],
                trim($member['first_name'] . ' ' . $member['last_name']), $member['phone'],
                $snapshot['payment_group'], $snapshot['monthly_contribution'], $snapshot['paid_amount'],
                $snapshot['balance_due'], $snapshot['missed_months'], $snapshot['last_payment_date'] ?? 'N/A',
                $detail['reason'], $detail['linked'], $detail['not_completed'], $detail['excluded_type'],
                $detail['missing_date'], $detail['qualifying'], $detail['completed_platinum'],
                $detail['current_month_qualifying'], $detail['invalid_date'], $detail['types'], $detail['statuses']];
            if (fputcsv($output, array_map(['PaymentNaDiagnostic', 'csvCell'], $row), ',', '"', '') === false) {
                throw new RuntimeException('Cannot write export');
            }
            $exported++;
        }
    } while (count($members) === 200);
    $pdo->rollBack();
    if ($memberNumber !== '' && $exported === 0) {
        fclose($output);
        http_response_code(404);
        exit('No active contribution-paying member matched that member number.');
    }
    rewind($output);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="payment-na-diagnostic-' . date('Ymd_His') . '.csv"');
    echo "\xEF\xBB\xBF";
    fpassthru($output);
    fclose($output);
} catch (Throwable $error) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    if (is_resource($output)) fclose($output);
    error_log('Payment N/A diagnostic failed: ' . get_class($error));
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Diagnostic export failed. No payment records were changed. Ask the administrator to check server logs.';
}
