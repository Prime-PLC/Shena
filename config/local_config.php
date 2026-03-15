<?php
/**
 * LOCAL DEVELOPMENT OVERRIDES — re-structure branch only
 *
 * This file is tracked in the `re-structure` branch but does NOT exist
 * in production branches (Feature-demo/main/etc.), so switching branches
 * automatically removes it — production is never touched.
 *
 * Loaded by config/config.php before any DB constants are defined.
 * Sets LOCAL_ env vars so envConfig() picks them up, AND sets $_SERVER
 * vars so Database.php can bypass production values entirely.
 */

define('LOCAL_OVERRIDE_APPLIED', true);

// ── Database ─────────────────────────────────────────────────────────────────
$_localDb = [
    'LOCAL_DB_HOST'    => '127.0.0.1',
    'LOCAL_DB_NAME'    => 'shena_welfare_db',
    'LOCAL_DB_USER'    => 'root',
    'LOCAL_DB_PASS'    => '4885',   // Change to '' if your XAMPP root has no password
    'LOCAL_DB_CHARSET' => 'utf8mb4',
    'LOCAL_DB_PORT'    => '3306',
];
foreach ($_localDb as $_k => $_v) {
    putenv("{$_k}={$_v}");
    $_ENV[$_k]    = $_v;
    $_SERVER[$_k] = $_v;
}
unset($_localDb, $_k, $_v);

// ── Application URL ───────────────────────────────────────────────────────────
putenv('LOCAL_APP_URL=http://localhost:8000');
$_ENV['LOCAL_APP_URL']    = 'http://localhost:8000';
$_SERVER['LOCAL_APP_URL'] = 'http://localhost:8000';
