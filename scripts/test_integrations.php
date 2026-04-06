<?php
/**
 * Integration connectivity test — run via CLI only:
 *   php scripts/test_integrations.php
 */

define('BASE_PATH', dirname(__DIR__));

// ── Load .env ─────────────────────────────────────────────────────────────────
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }
} else {
    die("[ERROR] .env file not found at {$envFile}\n");
}

function env(string $key, string $default = ''): string {
    return $_ENV[$key] ?? $default;
}

function pass(string $label): void { echo "\033[32m✔ PASS\033[0m  {$label}\n"; }
function fail(string $label, string $detail = ''): void {
    echo "\033[31m✘ FAIL\033[0m  {$label}";
    if ($detail) echo " — {$detail}";
    echo "\n";
}
function section(string $title): void {
    echo "\n\033[1;34m── {$title} ──\033[0m\n";
}

// ── 1. M-Pesa OAuth token ──────────────────────────────────────────────────────
section('M-Pesa OAuth (production)');

$mpesaEnv  = env('MPESA_ENVIRONMENT', 'sandbox');
$consKey   = env('MPESA_CONSUMER_KEY');
$consSec   = env('MPESA_CONSUMER_SECRET');
$baseUrl   = $mpesaEnv === 'production'
    ? 'https://api.safaricom.co.ke'
    : 'https://sandbox.safaricom.co.ke';
$oauthUrl  = $baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
$credentials = base64_encode("{$consKey}:{$consSec}");

echo "  Endpoint : {$oauthUrl}\n";
echo "  Env      : {$mpesaEnv}\n";
echo "  Key      : " . substr($consKey, 0, 8) . "...\n";

// Provide CA bundle for local Windows dev (XAMPP); on Linux this is handled by the OS
$caBundles = [
    'C:/xampp/apache/bin/curl-ca-bundle.crt',
    'C:/xampp/php/extras/ssl/cacert.pem',
    '/etc/ssl/certs/ca-certificates.crt',  // Linux
];
$caBundle = null;
foreach ($caBundles as $b) { if (file_exists($b)) { $caBundle = $b; break; } }

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $oauthUrl,
    CURLOPT_HTTPHEADER     => ["Authorization: Basic {$credentials}", "Content-Type: application/json"],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_CAINFO         => $caBundle,
    CURLOPT_TIMEOUT        => 15,
]);
$resp     = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

echo "  HTTP     : {$httpCode}\n";
echo "  Response : {$resp}\n";

if ($httpCode === 200) {
    $data  = json_decode($resp, true);
    $token = $data['access_token'] ?? null;
    if ($token) {
        pass("M-Pesa token obtained: " . substr($token, 0, 20) . "...");
    } else {
        fail("M-Pesa token present but access_token missing in response");
    }
} elseif ($curlErr) {
    fail("M-Pesa cURL error", $curlErr);
} else {
    fail("M-Pesa OAuth returned HTTP {$httpCode}");
}

// ── 2. HostPinnacle SMS ────────────────────────────────────────────────────────
section('HostPinnacle SMS API');

$smsUrl      = 'https://smsportal.hostpinnacle.co.ke/SMSApi/send';
$smsUserId   = env('HOSTPINNACLE_USER_ID');
$smsApiKey   = env('HOSTPINNACLE_API_KEY');
$smsSender   = env('HOSTPINNACLE_SENDER_ID', 'SHENA');
$smsTarget   = env('ADMIN_PHONE', '+254748585067');
$smsPhone    = ltrim(preg_replace('/^\+/', '', $smsTarget), '0'); // → 254XXXXXXXXX

echo "  Endpoint : {$smsUrl}\n";
echo "  User ID  : {$smsUserId}\n";
echo "  API Key  : " . substr($smsApiKey, 0, 8) . "...\n";
echo "  Sender ID: {$smsSender}\n";
echo "  To       : {$smsPhone}\n";

$smsData = http_build_query([
    'userid'         => $smsUserId,
    'password'       => $smsApiKey,
    'mobile'         => $smsPhone,
    'msg'            => 'Shena integration test - system connectivity check.',  // ASCII only (no Unicode)
    'senderid'       => $smsSender,
    'sendMethod'     => 'quick',
    'msgType'        => 'text',
    'duplicatecheck' => 'true',
    'output'         => 'json',
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $smsUrl,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $smsData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
]);
$smsResp     = curl_exec($ch);
$smsHttp     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$smsCurlErr  = curl_error($ch);
curl_close($ch);

echo "  HTTP     : {$smsHttp}\n";
echo "  Response : {$smsResp}\n";

if ($smsCurlErr) {
    fail("SMS cURL error", $smsCurlErr);
} elseif ($smsHttp === 200) {
    $result = json_decode($smsResp, true);
    $status = $result['status'] ?? null;
    $statusCode = (string)($result['statusCode'] ?? '');
    if ($status === 'success' || $statusCode === '200') {
        pass("SMS accepted by HostPinnacle — transactionId: " . ($result['transactionId'] ?? 'n/a'));
    } else {
        $reason = $result['reason'] ?? $result['message'] ?? 'unknown';
        fail("SMS rejected by API", "status={$status}, statusCode={$statusCode}, reason={$reason}");
    }
} else {
    fail("SMS API returned HTTP {$smsHttp}");
}

// ── 3. SMTP connection ─────────────────────────────────────────────────────────
section('SMTP Connection (PHPMailer)');

$smtpHost = env('MAIL_HOST', 'mail.shenacompanion.co.ke');
$smtpPort = (int) env('MAIL_PORT', '587');
$smtpUser = env('MAIL_USERNAME');
$smtpPass = env('MAIL_PASSWORD');
$smtpFrom = env('MAIL_FROM_EMAIL');

echo "  Host     : {$smtpHost}:{$smtpPort}\n";
echo "  Username : {$smtpUser}\n";
echo "  From     : {$smtpFrom}\n";

// Use raw socket to verify the host is reachable first
$sock = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 10);
if (!$sock) {
    fail("Cannot reach {$smtpHost}:{$smtpPort}", "{$errno} {$errstr}");
} else {
    $banner = fgets($sock, 256);
    fclose($sock);
    pass("SMTP socket open — banner: " . trim($banner));

    // Now test AUTH with PHPMailer if available
    $mailerPath = BASE_PATH . '/vendor/phpmailer/src/PHPMailer.php';
    if (!file_exists($mailerPath)) {
        $mailerPath = BASE_PATH . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    }
    if (file_exists($mailerPath)) {
        require_once dirname($mailerPath) . '/PHPMailer.php';
        require_once dirname($mailerPath) . '/SMTP.php';
        require_once dirname($mailerPath) . '/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpPort;
            $mail->SMTPDebug  = 0;  // Set to 2 for verbose
            if ($mail->smtpConnect()) {
                pass("SMTP authentication succeeded");
                $mail->smtpClose();
            } else {
                fail("SMTP connection failed (check credentials)");
            }
        } catch (Exception $e) {
            fail("SMTP exception", $e->getMessage());
        }
    } else {
        echo "  [INFO] PHPMailer not found in vendor — skipping AUTH test\n";
    }
}

// ── Summary ────────────────────────────────────────────────────────────────────
section('Done');
echo "Review output above. Fix any FAIL items before deploying.\n\n";
