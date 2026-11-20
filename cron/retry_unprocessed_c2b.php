<?php
/**
 * Retry M-Pesa C2B callbacks that were stored but never became a payment
 * (for example the database failed mid-processing).
 *
 * Cron (every 10 minutes):
 *   *\/10 * * * * /usr/bin/php /path/to/Shena/cron/retry_unprocessed_c2b.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/app/core/Database.php';
require_once ROOT_PATH . '/app/core/BaseModel.php';
require_once ROOT_PATH . '/app/models/Payment.php';
require_once ROOT_PATH . '/app/models/Member.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/helpers/functions.php';
require_once ROOT_PATH . '/app/services/SmsService.php';
require_once ROOT_PATH . '/app/services/PaymentReconciliationService.php';

$service = new PaymentReconciliationService();
$summary = $service->retryUnprocessedCallbacks(50, 5);

echo date('Y-m-d H:i:s') . ' C2B retry: ' . json_encode($summary) . PHP_EOL;
