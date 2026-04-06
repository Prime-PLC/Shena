<?php
/**
 * Application Configuration
 */

// Note: Many scripts that include this file define `ROOT_PATH` before requiring it.

// Environment Settings - Simple .env loader
if (defined('ROOT_PATH') && file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2) + [NULL, NULL];
        if ($name && $value !== null) {
            $name = trim($name);
            $value = trim($value);
            // Handle comments at end of line
            $value = explode('#', $value, 2)[0];
            $value = trim($value);
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}

if (!function_exists('envConfig')) {
    function envConfig($key, $default = null)
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }

        return $default;
    }
}

define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true'); // CRITICAL: Set to false in production
define('APP_NAME', getenv('APP_NAME') ?: 'Shena Companion Welfare Association');

// ── Local override (re-structure branch) ─────────────────────────────────────
// config/local_config.php is only present in the re-structure branch.
// It runs before any constants are defined, injects LOCAL_ env vars into
// $_SERVER so envConfig() picks them up, and forces $isLocalEnvironment=true
// even for plain CLI runs (php script.php).
if (file_exists(__DIR__ . '/local_config.php')) {
    require_once __DIR__ . '/local_config.php';
}

$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocalHost = preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/i', $httpHost) === 1;
$isLocalCliServer = PHP_SAPI === 'cli-server';
// Also treat as local when local_config.php signalled LOCAL_OVERRIDE_APPLIED
$isLocalEnvironment = $isLocalHost || $isLocalCliServer || defined('LOCAL_OVERRIDE_APPLIED');

$appUrl = getenv('APP_URL') ?: 'http://localhost';
if ($isLocalEnvironment) {
    $appUrl = getenv('LOCAL_APP_URL') ?: 'http://localhost:8000';
}
define('APP_URL', $appUrl);

// Database Configuration
$dbHost = envConfig('DB_HOST', 'localhost');
$dbName = envConfig('DB_NAME', 'shena_welfare_db');
$dbUser = envConfig('DB_USER', 'root');
$dbPass = envConfig('DB_PASS', '4885');

if ($isLocalEnvironment) {
    $dbHost = envConfig('LOCAL_DB_HOST', '127.0.0.1');
    $dbName = envConfig('LOCAL_DB_NAME', 'shena_welfare_db');
    $dbUser = envConfig('LOCAL_DB_USER', 'root');
    $dbPass = envConfig('LOCAL_DB_PASS', '4885');
}

define('DB_HOST', $dbHost);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_CHARSET', 'utf8mb4');
define('DB_FILE', (defined('ROOT_PATH') ? ROOT_PATH : __DIR__ . '/..') . '/database/shena_welfare.db');

// M-Pesa Configuration
define('MPESA_ENVIRONMENT', envConfig('MPESA_ENVIRONMENT', 'sandbox')); // sandbox or production
define('MPESA_CONSUMER_KEY', envConfig('MPESA_CONSUMER_KEY', ''));
define('MPESA_CONSUMER_SECRET', envConfig('MPESA_CONSUMER_SECRET', ''));

// Sandbox shortcode for testing (174379)
define('MPESA_SANDBOX_SHORTCODE', '174379');
define('MPESA_SANDBOX_PASSKEY', envConfig('MPESA_SANDBOX_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'));

// Production shortcode (4163987)
define('MPESA_PRODUCTION_SHORTCODE', '4163987');
define('MPESA_PRODUCTION_PASSKEY', envConfig('MPESA_PRODUCTION_PASSKEY', ''));

// Active shortcode based on environment
define('MPESA_BUSINESS_SHORTCODE', MPESA_ENVIRONMENT === 'production' ? MPESA_PRODUCTION_SHORTCODE : MPESA_SANDBOX_SHORTCODE);
define('MPESA_PASSKEY', MPESA_ENVIRONMENT === 'production' ? MPESA_PRODUCTION_PASSKEY : MPESA_SANDBOX_PASSKEY);

// Callback URLs
define('MPESA_STK_CALLBACK_URL', envConfig('MPESA_STK_CALLBACK_URL', APP_URL . '/public/mpesa-stk-callback.php'));
define('MPESA_C2B_CALLBACK_URL', envConfig('MPESA_C2B_CALLBACK_URL', APP_URL . '/public/mpesa-c2b-callback.php'));
define('MPESA_CALLBACK_URL', MPESA_STK_CALLBACK_URL); // Default to STK callback

// Email Configuration (SMTP)
define('MAIL_ENABLED', filter_var((string) envConfig('MAIL_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN));
define('MAIL_HOST', envConfig('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', (int) envConfig('MAIL_PORT', 587));
define('MAIL_USERNAME', envConfig('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', envConfig('MAIL_PASSWORD', ''));
define('MAIL_FROM_EMAIL', envConfig('MAIL_FROM_EMAIL', 'noreply@shenacompanion.org'));
define('MAIL_FROM_NAME', APP_NAME);

// HostPinnacle SMS Configuration
define('HOSTPINNACLE_USER_ID', envConfig('HOSTPINNACLE_USER_ID', 'oscar'));
define('HOSTPINNACLE_API_KEY', envConfig('HOSTPINNACLE_API_KEY', ''));
define('HOSTPINNACLE_SENDER_ID', envConfig('HOSTPINNACLE_SENDER_ID', 'SHENA'));
define('HOSTPINNACLE_SMS_API_URL', envConfig('HOSTPINNACLE_SMS_API_URL', 'https://smsportal.hostpinnacle.co.ke/SMSApi/send'));

// Security Settings - CRITICAL: Change these in production
define('ENCRYPTION_KEY', envConfig('ENCRYPTION_KEY', bin2hex(random_bytes(16))));
define('JWT_SECRET', envConfig('JWT_SECRET', bin2hex(random_bytes(32))));
define('SESSION_LIFETIME', 7200); // 2 hours

// File Upload Settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', (defined('ROOT_PATH') ? ROOT_PATH : __DIR__ . '/..') . '/storage/uploads');

// Payment Settings
define('REGISTRATION_FEE', 200); // Ksh. 10 (Testing)
define('REACTIVATION_FEE', 100); // Ksh. 100
define('MORTUARY_DAYS_COVERED', 14); // Maximum days of mortuary covered

// Contact Information
define('ADMIN_EMAIL', envConfig('ADMIN_EMAIL', 'info@shenacompanion.org'));
define('ADMIN_PHONE', envConfig('ADMIN_PHONE', '+254712345678'));
define('OFFICE_ADDRESS', 'Shena Companion Welfare Association Office, Nairobi, Kenya');

// Membership Maturity & Grace Period Settings (in months)
define('MATURITY_PERIOD_UNDER_80', 4); // 4 months for ages 1-80 years
define('MATURITY_PERIOD_80_AND_ABOVE', 5); // 5 months for ages 81-100 years

// GRACE PERIOD
define('GRACE_PERIOD_MONTHS', 2); // 2 months max before default

// Load canonical packages config if present
if (defined('ROOT_PATH') && file_exists(ROOT_PATH . '/config/packages.php')) {
    $membership_packages = require ROOT_PATH . '/config/packages.php';
} elseif (file_exists(__DIR__ . '/packages.php')) {
    $membership_packages = require __DIR__ . '/packages.php';
} else {
    $membership_packages = [];
}

// Core Services Available in All Packages
$core_services = [
    'mortuary_bill' => [
        'name' => 'Mortuary Bill Payment',
        'description' => 'Payment of mortuary preservation costs up to 14 days',
        'max_days' => 14,
        'includes' => 'Preservation costs only (excludes admission and dressing charges)'
    ],
    'body_dressing' => [
        'name' => 'Body Dressing',
        'description' => 'Professional and dignified body dressing services',
        'includes' => 'Preparation for viewing and burial as per cultural/religious preferences'
    ],
    'transportation' => [
        'name' => 'Body Transportation',
        'description' => 'Secure and respectful transportation of deceased',
        'includes' => 'From mortuary to funeral venue and final resting place'
    ],
    'coffin' => [
        'name' => 'Executive Coffin',
        'description' => 'High-quality executive coffin',
        'includes' => 'Suitable for viewing and burial'
    ],
    'burial_equipment' => [
        'name' => 'Burial Equipment',
        'description' => 'Professional burial equipment and ceremony setup',
        'includes' => 'Lowering gear, trolley, gazebo tent, and 100 chairs'
    ]
];

// Timezone
date_default_timezone_set('Africa/Nairobi');
