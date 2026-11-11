<?php
if (PHP_SAPI !== 'cli') exit('CLI only');

define('MPESA_CONSUMER_KEY', 'test-key');
define('MPESA_CONSUMER_SECRET', 'test-secret');
define('MPESA_BUSINESS_SHORTCODE', '123456');
define('MPESA_PASSKEY', 'test-passkey');
define('MPESA_CALLBACK_URL', 'https://example.test/callback');
define('MPESA_ENVIRONMENT', 'sandbox');

require_once __DIR__ . '/../app/services/PaymentService.php';

class PaymentServiceWithStatus extends PaymentService
{
    public $status;

    public function queryTransactionStatus($checkoutRequestId)
    {
        return $this->status;
    }
}

function assertCallbackVerification(bool $expected, $status, array $callback, array $payment): void
{
    $service = new PaymentServiceWithStatus();
    $service->status = $status;
    $actual = $service->verifySuccessfulCallback($callback, $payment);
    if ($actual !== $expected) {
        throw new RuntimeException('Unexpected callback verification result.');
    }
}

$callback = [
    'Body' => [
        'stkCallback' => [
            'MerchantRequestID' => 'merchant-123',
            'CheckoutRequestID' => 'checkout-123',
            'ResultCode' => 0,
            'CallbackMetadata' => [
                'Item' => [
                    ['Name' => 'Amount', 'Value' => 200],
                    ['Name' => 'MpesaReceiptNumber', 'Value' => 'ABC123'],
                    ['Name' => 'PhoneNumber', 'Value' => 254712345678],
                ],
            ],
        ],
    ],
];
$payment = [
    'merchant_request_id' => 'merchant-123',
    'checkout_request_id' => 'checkout-123',
    'amount' => 200,
    'phone_number' => '254712345678',
];

assertCallbackVerification(true, ['ResultCode' => '0'], $callback, $payment);
assertCallbackVerification(true, false, $callback, $payment);
assertCallbackVerification(true, ['ResponseCode' => '0'], $callback, $payment);
assertCallbackVerification(false, ['ResultCode' => '1'], $callback, $payment);

$mismatchedAmount = $callback;
$mismatchedAmount['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'] = 300;
assertCallbackVerification(false, false, $mismatchedAmount, $payment);

echo "STK callback verification fallback tests passed.\n";
