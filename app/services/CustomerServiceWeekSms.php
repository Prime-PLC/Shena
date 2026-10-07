<?php
/** One-off 2026 appreciation wording, selected when an SMS is generated. */
final class CustomerServiceWeekSms
{
    public const APPRECIATION = 'Happy Customer Service Week! Thank you for trusting Shena Companion. We value you.';

    public static function isActive(?DateTimeImmutable $now = null): bool
    {
        $zone = new DateTimeZone('Africa/Nairobi');
        $day = ($now ?? new DateTimeImmutable('now', $zone))->setTimezone($zone)->format('Y-m-d');
        return $day >= '2026-10-05' && $day <= '2026-10-09';
    }

    public static function appreciate(string $message, ?DateTimeImmutable $now = null): string
    {
        if (!self::isActive($now)) return $message;
        return rtrim($message) . ' ' . self::APPRECIATION;
    }

    public static function paymentConfirmation(array $data, ?DateTimeImmutable $now = null): string
    {
        $receipt = "Payment confirmed! KES {$data['amount']} received. Transaction ID: {$data['transaction_id']}.";
        return self::isActive($now)
            ? $receipt . ' ' . self::APPRECIATION
            : $receipt . ' Thank you. - Shena Companion';
    }
}
