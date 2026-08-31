<?php
declare(strict_types=1);

/**
 * Read-only production schema check.
 *
 * Set DB_HEALTH_CHECK_TOKEN to a long, random value in the production .env,
 * then open /database-health-check.php?token=that-value while logged out of
 * any shared screen. Delete this file after the verification is complete.
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/app/core/Database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow');

$expectedToken = envConfig('DB_HEALTH_CHECK_TOKEN', '');
$providedToken = (string) ($_GET['token'] ?? '');
if ($expectedToken === '' || $providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden. Configure DB_HEALTH_CHECK_TOKEN and provide it as the token query parameter.']);
    exit;
}

/** @return bool */
function schemaTableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1');
    $statement->execute([$table]);
    return (bool) $statement->fetchColumn();
}

/** @return bool */
function schemaColumnExists(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare('SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1');
    $statement->execute([$table, $column]);
    return (bool) $statement->fetchColumn();
}

try {
    $pdo = Database::getInstance()->getConnection();
    $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();

    // This is the exact, intentionally limited manifest used by
    // scripts/deploy_migrations.php. Older repository migrations predate the
    // migration ledger and therefore cannot be claimed as applied from a row.
    $trackedManifest = [
        '018_platinum_foundation.sql',
        '019_admin_created_claims.sql',
        '020_platinum_payment_integration.sql',
        '021_platinum_overrides_and_admin_flows.sql',
        '022_platinum_group_coverage_and_cumulative_billing.sql',
        '023_legacy_medical_placeholder_audit.sql',
        '024_platinum_admin_approval_only.sql',
    ];
    $hasLedger = schemaTableExists($pdo, 'schema_migrations');
    $applied = [];
    if ($hasLedger) {
        $applied = $pdo->query('SELECT migration FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
    }
    $missingTrackedMigrations = array_values(array_diff($trackedManifest, $applied));

    $requiredSchema = [
        'member_corporate_members' => ['member_id', 'package_key', 'monthly_contribution', 'date_of_birth'],
        'platinum_coverages' => ['member_id', 'covered_person_type', 'package_key', 'package_name', 'monthly_contribution', 'status', 'approved_at'],
        'platinum_day_ledgers' => ['platinum_coverage_id', 'calendar_year', 'annual_limit'],
        'platinum_payment_allocations' => ['payment_id', 'platinum_coverage_id', 'allocated_amount', 'allocation_month'],
        'inpatient_requests' => ['platinum_coverage_id', 'covered_person_type', 'covered_person_id'],
        'legacy_medical_corporate_archive' => ['legacy_corporate_member_id'],
        'payments' => ['payment_type', 'platinum_coverage_id'],
        'beneficiaries' => ['coverage_owner_type', 'coverage_owner_id'],
        'bulk_messages' => ['status', 'total_recipients', 'submitted_count', 'delivered_count'],
        'bulk_message_recipients' => ['bulk_message_id', 'status', 'processing_token', 'provider_message_id', 'submitted_at', 'delivered_at'],
        'sms_queue' => ['status'],
    ];
    $missingSchema = [];
    foreach ($requiredSchema as $table => $columns) {
        if (!schemaTableExists($pdo, $table)) {
            $missingSchema[$table] = ['table is missing'];
            continue;
        }
        foreach ($columns as $column) {
            if (!schemaColumnExists($pdo, $table, $column)) {
                $missingSchema[$table][] = 'missing column: ' . $column;
            }
        }
    }

    $migrationFiles = glob(ROOT_PATH . '/database/migrations/*.{sql,php}', GLOB_BRACE) ?: [];
    $repositoryMigrations = array_map('basename', $migrationFiles);
    sort($repositoryMigrations);
    $untrackedRepositoryMigrations = array_values(array_diff($repositoryMigrations, $trackedManifest));

    $ok = $hasLedger && empty($missingTrackedMigrations) && empty($missingSchema);
    http_response_code($ok ? 200 : 503);
    echo json_encode([
        'ok' => $ok,
        'database' => $database,
        'checked_at_utc' => gmdate('c'),
        'tracked_manifest' => [
            'ledger_present' => $hasLedger,
            'required' => $trackedManifest,
            'missing_records' => $missingTrackedMigrations,
        ],
        'required_schema' => [
            'missing' => $missingSchema,
        ],
        'repository_history_note' => 'Only migrations 018-024 are recorded by the supplied deployment runner. The following older files have no reliable application ledger and require schema-based/manual review; do not run them merely because they appear here.',
        'repository_migrations_not_ledger_verified' => $untrackedRepositoryMigrations,
        'next_step' => $ok
            ? 'Tracked Platinum/SMS schema requirements are present.'
            : 'Do not deploy or run migrations from this page. Apply the reported migration/schema fix through a reviewed deployment, then run this check again.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Database check could not connect or query the schema.']);
}
