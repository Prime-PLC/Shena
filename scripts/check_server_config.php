<?php
/**
 * Post-upload server configuration checker.
 * Run ONCE after uploading files to HostPinnacle:
 *   php scripts/check_server_config.php
 *
 * Delete or restrict this file after running in production.
 */

define('BASE_PATH', dirname(__DIR__));

// Load .env
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }
}

function env(string $k, string $d = ''): string { return $_ENV[$k] ?? $d; }
function ok(string $msg): void  { echo "\033[32m✔\033[0m  {$msg}\n"; }
function warn(string $msg): void { echo "\033[33m⚠\033[0m  {$msg}\n"; }
function fail(string $msg): void { echo "\033[31m✘\033[0m  {$msg}\n"; }
function section(string $t): void { echo "\n\033[1;34m── {$t} ──\033[0m\n"; }

// ── PHP version ────────────────────────────────────────────────────────────────
section('PHP Environment');
$ver = PHP_VERSION;
version_compare($ver, '8.0.0', '>=') ? ok("PHP {$ver}") : fail("PHP {$ver} — need 8.0+");

// Required extensions
foreach (['pdo', 'pdo_mysql', 'curl', 'mbstring', 'openssl', 'json', 'fileinfo'] as $ext) {
    extension_loaded($ext) ? ok("ext-{$ext}") : fail("ext-{$ext} NOT loaded");
}

// ── .env loaded ────────────────────────────────────────────────────────────────
section('.env File');
file_exists($envFile) ? ok(".env found at {$envFile}") : fail(".env NOT found — upload it via FTP/cPanel");
env('DEBUG_MODE') === 'false' ? ok("DEBUG_MODE=false") : warn("DEBUG_MODE=" . env('DEBUG_MODE') . " — set to false for production");
env('APP_URL')    ? ok("APP_URL=" . env('APP_URL'))    : fail("APP_URL not set");
env('DB_NAME')    ? ok("DB_NAME=" . env('DB_NAME'))    : fail("DB_NAME not set");
env('MPESA_CONSUMER_KEY') ? ok("MPESA_CONSUMER_KEY set") : fail("MPESA_CONSUMER_KEY not set");
env('MPESA_PRODUCTION_PASSKEY') && env('MPESA_PRODUCTION_PASSKEY') !== 'REPLACE_WITH_PROD_PASSKEY_FROM_SAFARICOM'
    ? ok("MPESA_PRODUCTION_PASSKEY set")
    : warn("MPESA_PRODUCTION_PASSKEY is still a placeholder — get it from Daraja portal");
env('HOSTPINNACLE_API_KEY') ? ok("HOSTPINNACLE_API_KEY set") : fail("HOSTPINNACLE_API_KEY not set");
env('ENCRYPTION_KEY') ? ok("ENCRYPTION_KEY set") : fail("ENCRYPTION_KEY not set");

// ── Directory permissions ──────────────────────────────────────────────────────
section('Directory Permissions');
$dirs = [
    BASE_PATH . '/storage/logs'   => 'writable',
    BASE_PATH . '/storage/uploads' => 'writable',
    BASE_PATH . '/config'          => 'readable (not writable)',
    BASE_PATH . '/database'        => 'readable (not writable)',
];
foreach ($dirs as $dir => $desc) {
    if (!is_dir($dir)) {
        fail("Directory missing: {$dir}");
        continue;
    }
    $writable = is_writable($dir);
    $short = str_replace(BASE_PATH . '/', '', $dir);
    if (str_contains($desc, 'writable')) {
        $writable ? ok("{$short}/ is writable") : fail("{$short}/ is NOT writable — run: chmod 755 {$short}");
    } else {
        $writable ? warn("{$short}/ is writable (consider restricting)") : ok("{$short}/ is read-only");
    }
}

// ── Database connection ────────────────────────────────────────────────────────
section('Database Connection');
try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', env('DB_HOST','localhost'), env('DB_NAME'));
    $pdo = new PDO($dsn, env('DB_USER'), env('DB_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    ok("Connected to MySQL as " . env('DB_USER') . "@" . env('DB_HOST'));
    $tbls = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    ok(count($tbls) . " tables found: " . implode(', ', array_slice($tbls, 0, 8)) . (count($tbls) > 8 ? '...' : ''));
    $required = ['users','members','payments','claims','agents','notifications','support_tickets','mpesa_stk_push_logs'];
    foreach ($required as $t) {
        in_array($t, $tbls) ? ok("Table {$t} exists") : fail("Table {$t} MISSING — re-import shena_production.sql");
    }
} catch (PDOException $e) {
    fail("DB connection failed: " . $e->getMessage());
}

// ── .htaccess ─────────────────────────────────────────────────────────────────
section('.htaccess');
$htaccess = BASE_PATH . '/.htaccess';
if (file_exists($htaccess)) {
    $content = file_get_contents($htaccess);
    str_contains($content, 'RewriteEngine') ? ok(".htaccess present with mod_rewrite rules") : warn(".htaccess exists but no RewriteEngine — mod_rewrite may not be enabled");
    str_contains($content, 'https://') ? ok("HTTPS redirect rule found") : warn("No HTTPS redirect in .htaccess");
} else {
    fail(".htaccess NOT found");
}

// ── Sensitive path protection ──────────────────────────────────────────────────
section('Sensitive Paths Protected');
$htContent = file_exists($htaccess) ? file_get_contents($htaccess) : '';
foreach (['storage/logs', 'cron', 'scripts', 'archives'] as $path) {
    str_contains($htContent, $path) ? ok("/{$path} is blocked in .htaccess") : warn("/{$path} not explicitly blocked");
}
file_exists(BASE_PATH . '/public/diag-admin-login.php') ? fail("diag-admin-login.php still exists — DELETE IT") : ok("diag-admin-login.php deleted");

// ── Summary ────────────────────────────────────────────────────────────────────
section('Summary');
echo "Review FAILs above.\n";
echo "After confirming all checks pass, delete or restrict this script on the server.\n\n";
