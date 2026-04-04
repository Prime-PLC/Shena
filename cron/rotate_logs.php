<?php
/**
 * Log rotation script – delete log files older than 30 days.
 * Schedule: daily via cPanel Cron Jobs
 *   0 2 * * * php /home/username/public_html/cron/rotate_logs.php
 */

define('ROOT_PATH', dirname(__DIR__));
define('LOG_RETENTION_DAYS', 30);

$logDir  = ROOT_PATH . '/storage/logs';
$cutoff  = time() - (LOG_RETENTION_DAYS * 86400);
$deleted = 0;
$errors  = 0;

if (!is_dir($logDir)) {
    exit(0);
}

$iterator = new DirectoryIterator($logDir);
foreach ($iterator as $file) {
    if ($file->isDot() || $file->isDir()) {
        continue;
    }

    // Only rotate dated log files (e.g. mpesa_stk_2026-01-15.log, error.log kept)
    $name = $file->getFilename();
    if ($file->getMTime() < $cutoff && str_ends_with($name, '.log') && $name !== 'error.log') {
        if (@unlink($file->getPathname())) {
            $deleted++;
        } else {
            $errors++;
            error_log("[rotate_logs] Failed to delete: " . $file->getPathname());
        }
    }
}

$summary = date('Y-m-d H:i:s') . " – Log rotation: deleted={$deleted}, errors={$errors}\n";
file_put_contents($logDir . '/error.log', $summary, FILE_APPEND);
