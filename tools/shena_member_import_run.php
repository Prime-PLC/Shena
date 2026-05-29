<?php

/**
 * Production importer for cleaned SHENA member CSV files.
 *
 * Dry run:
 *   php tools/shena_member_import_run.php storage/imports/.../members_import_clean.csv --dry-run
 *
 * Insert:
 *   php tools/shena_member_import_run.php storage/imports/.../members_import_clean.csv --commit
 */

if ($argc < 3 || !in_array($argv[2], ['--dry-run', '--commit'], true)) {
    fwrite(STDERR, "Usage: php tools/shena_member_import_run.php <members_import_clean.csv> --dry-run|--commit\n");
    exit(1);
}

$csvPath = $argv[1];
$commit = $argv[2] === '--commit';

if (!is_file($csvPath)) {
    fwrite(STDERR, "CSV not found: {$csvPath}\n");
    exit(1);
}

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once CONFIG_PATH . '/config.php';
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/core/BaseModel.php';
require_once APP_PATH . '/core/BaseController.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Member.php';
require_once APP_PATH . '/helpers/MemberNumberHelper.php';
require_once APP_PATH . '/controllers/AuthController.php';

$db = Database::getInstance();
$userModel = new User();
$memberModel = new Member();
$rows = readCsvAssoc($csvPath);
$created = [];
$skipped = [];
$errors = [];

try {
    $db->getConnection()->beginTransaction();

    foreach ($rows as $idx => $row) {
        $line = $idx + 2;
        $phone = trim((string)($row['phone'] ?? ''));
        $idNumber = trim((string)($row['id_number'] ?? ''));
        $email = trim((string)($row['email'] ?? ''));

        if ($phone === '' || $idNumber === '') {
            $skipped[] = ['line' => $line, 'reason' => 'Missing phone or ID number'];
            continue;
        }
        if ($userModel->findByPhone($phone)) {
            $skipped[] = ['line' => $line, 'reason' => 'Phone already exists', 'phone' => $phone];
            continue;
        }
        if ($email !== '' && $userModel->findByEmail($email)) {
            $skipped[] = ['line' => $line, 'reason' => 'Email already exists', 'email' => $email];
            continue;
        }
        if ($memberModel->findByIdNumber($idNumber)) {
            $skipped[] = ['line' => $line, 'reason' => 'ID number already exists', 'id_number' => $idNumber];
            continue;
        }

        $placeholderPassword = bin2hex(random_bytes(16));
        $userId = $userModel->create([
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $email,
            'phone' => $phone,
            'password' => password_hash($placeholderPassword, PASSWORD_DEFAULT),
            'role' => 'member',
            'status' => 'active',
        ]);

        $memberNumber = MemberNumberHelper::generateCanonical();
        $memberId = $memberModel->create([
            'user_id' => $userId,
            'member_number' => $memberNumber,
            'id_number' => $idNumber,
            'date_of_birth' => $row['date_of_birth'],
            'gender' => $row['gender'],
            'address' => $row['address'] ?? '',
            'package' => $row['package'] ?: 'individual',
            'package_key' => $row['package_key'],
            'monthly_contribution' => (float)$row['monthly_contribution'],
            'status' => 'active',
            'pending_payment_type' => null,
        ]);

        $inviteToken = AuthController::generateInviteToken((int)$userId);
        $inviteLink = rtrim(APP_URL, '/') . '/set-password?token=' . urlencode($inviteToken);
        $loginLink = rtrim(APP_URL, '/') . '/login';
        $forgotPasswordLink = rtrim(APP_URL, '/') . '/forgot-password';
        $created[] = [
            'source_row' => $row['source_row'] ?? $line,
            'user_id' => $userId,
            'member_id' => $memberId,
            'member_number' => $memberNumber,
            'name' => trim($row['first_name'] . ' ' . $row['last_name']),
            'phone' => $phone,
            'id_number' => $idNumber,
            'monthly_contribution' => $row['monthly_contribution'],
            'set_password_link' => $inviteLink,
            'login_link' => $loginLink,
            'forgot_password_link' => $forgotPasswordLink,
            'sms_message' => 'Welcome to SHENA Companion. Your active member number is ' . $memberNumber . '. To access your portal, open ' . $forgotPasswordLink . ' and set your password using your registered phone.',
        ];
    }

    if ($commit) {
        $db->getConnection()->commit();
    } else {
        $db->getConnection()->rollBack();
    }
} catch (Throwable $e) {
    if ($db->getConnection()->inTransaction()) {
        $db->getConnection()->rollBack();
    }
    $errors[] = ['message' => $e->getMessage()];
}

$outDir = dirname($csvPath);
writeCsv($outDir . '/members_import_created_' . ($commit ? 'commit' : 'dry_run') . '.csv', $created, [
    'source_row',
    'user_id',
    'member_id',
    'member_number',
    'name',
    'phone',
    'id_number',
    'monthly_contribution',
    'set_password_link',
    'login_link',
    'forgot_password_link',
    'sms_message',
]);
writeCsv($outDir . '/members_import_skipped_' . ($commit ? 'commit' : 'dry_run') . '.csv', $skipped, ['line', 'reason', 'phone', 'email', 'id_number']);
file_put_contents($outDir . '/members_import_run_summary_' . ($commit ? 'commit' : 'dry_run') . '.json', json_encode([
    'mode' => $commit ? 'commit' : 'dry_run',
    'created_count' => count($created),
    'skipped_count' => count($skipped),
    'error_count' => count($errors),
    'errors' => $errors,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo ($commit ? "COMMIT" : "DRY RUN") . " complete\n";
echo "Created: " . count($created) . "\n";
echo "Skipped: " . count($skipped) . "\n";
echo "Errors: " . count($errors) . "\n";
if ($errors) {
    exit(1);
}

function readCsvAssoc(string $path): array
{
    $fh = fopen($path, 'r');
    $headers = fgetcsv($fh);
    $rows = [];
    while (($line = fgetcsv($fh)) !== false) {
        $row = [];
        foreach ($headers as $i => $header) {
            $row[$header] = $line[$i] ?? '';
        }
        $rows[] = $row;
    }
    fclose($fh);
    return $rows;
}

function writeCsv(string $path, array $rows, array $headers): void
{
    $fh = fopen($path, 'w');
    fputcsv($fh, $headers);
    foreach ($rows as $row) {
        $line = [];
        foreach ($headers as $header) {
            $line[] = $row[$header] ?? '';
        }
        fputcsv($fh, $line);
    }
    fclose($fh);
}
