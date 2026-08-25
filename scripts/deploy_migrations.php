<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';

$expectedDatabase = getenv('SHENA_MIGRATION_DB_NAME') ?: '';
if ($expectedDatabase === '' || DB_NAME !== $expectedDatabase) {
    fwrite(STDERR, "Migration aborted: configured database does not match the deployment allowlist.\n");
    exit(1);
}

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$lock = $pdo->query("SELECT GET_LOCK('shena_schema_migrations', 60)")->fetchColumn();
if ((int) $lock !== 1) {
    throw new RuntimeException('Could not obtain the schema migration lock.');
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS schema_migrations (migration VARCHAR(120) PRIMARY KEY, applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
    $allowed = [
        '018_platinum_foundation.sql',
        '019_admin_created_claims.sql',
        '020_platinum_payment_integration.sql',
        '021_platinum_overrides_and_admin_flows.sql',
        '022_platinum_group_coverage_and_cumulative_billing.sql',
        '023_legacy_medical_placeholder_audit.sql',
        '024_platinum_admin_approval_only.sql',
    ];
    foreach ($allowed as $migration) {
        $applied = $pdo->prepare('SELECT 1 FROM schema_migrations WHERE migration = ?');
        $applied->execute([$migration]);
        if ($applied->fetchColumn()) {
            continue;
        }
        $path = ROOT_PATH . '/database/migrations/' . $migration;
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('Migration file could not be read: ' . $migration);
        }
        $pdo->beginTransaction();
        try {
            foreach (preg_split('/;\s*(?:\r?\n|$)/', preg_replace('/^\s*--.*$/m', '', $sql)) as $statement) {
                if (trim($statement) !== '') {
                    $pdo->exec($statement);
                }
            }
            $record = $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (?)');
            $record->execute([$migration]);
            if ($pdo->inTransaction()) {
                $pdo->commit();
            }
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    foreach (['platinum_coverages', 'platinum_day_ledgers', 'inpatient_requests', 'legacy_medical_corporate_archive'] as $table) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?');
        $check->execute([DB_NAME, $table]);
        if ((int) $check->fetchColumn() !== 1) {
            throw new RuntimeException('Migration verification failed for table: ' . $table);
        }
    }
} finally {
    $pdo->query("SELECT RELEASE_LOCK('shena_schema_migrations')");
}
