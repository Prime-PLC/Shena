<?php
/**
 * Shena Companion Welfare Association - Main Entry Point
 * Front Controller Pattern
 */

// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', defined('DEBUG_MODE') && DEBUG_MODE ? 0 : 1); // Enforce secure cookie over HTTPS in production
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Define constants
if (!defined('ROOT_PATH'))    define('ROOT_PATH',    __DIR__);
if (!defined('APP_PATH'))     define('APP_PATH',     ROOT_PATH . '/app');
if (!defined('PUBLIC_PATH'))  define('PUBLIC_PATH',  ROOT_PATH . '/public');
if (!defined('CONFIG_PATH'))  define('CONFIG_PATH',  ROOT_PATH . '/config');
if (!defined('VIEWS_PATH'))   define('VIEWS_PATH',   ROOT_PATH . '/resources/views');
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH', ROOT_PATH . '/storage/uploads');

// Load configuration first
require_once CONFIG_PATH . '/config.php';

// Set error reporting based on DEBUG_MODE
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/storage/logs/error.log');
}

// Auto-load classes
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/controllers/',
        APP_PATH . '/models/',
        APP_PATH . '/middleware/',
        APP_PATH . '/services/',
        APP_PATH . '/core/',
        APP_PATH . '/helpers/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load helper functions
if (file_exists(APP_PATH . '/helpers/functions.php')) {
    require_once APP_PATH . '/helpers/functions.php';
}

// Initialize the application
try {
    $router = new Router();
    $router->dispatch();
} catch (Throwable $e) {
    error_log('Application error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo '<h1>Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        if (file_exists(VIEWS_PATH . '/errors/500.php')) {
            $error_code = 'Internal Server Error';
            $error_message = 'An unexpected error occurred. Please try again later.';
            include VIEWS_PATH . '/errors/500.php';
        } else {
            echo 'An error occurred. Please try again later.';
        }
    }
}
