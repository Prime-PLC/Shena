<?php

/**
 * Agent commission rules, controlled by the system administrator (Settings > Agent Commission).
 *
 * Standing rule: agents earn ONE commission per registered member. Commission on member
 * contributions is OFF unless an administrator deliberately switches it on.
 */
require_once __DIR__ . '/SettingsService.php';

class CommissionPolicy
{
    public const KEY_CONTRIBUTION_ENABLED = 'agent_contribution_commission_enabled';
    public const KEY_REGISTRATION_AMOUNT = 'agent_registration_commission_amount';
    public const DEFAULT_REGISTRATION_AMOUNT = 200.0;
    public const MAX_REGISTRATION_AMOUNT = 100000.0;

    /** Anything other than an explicit "on" value means OFF (including a missing setting). */
    public static function normalizeEnabledValue($value): bool
    {
        return in_array(strtolower(trim((string)$value)), ['1', 'true', 'on', 'yes'], true);
    }

    /** A valid amount is a number from 0 to MAX_REGISTRATION_AMOUNT; anything else is null. */
    public static function normalizeAmount($value): ?float
    {
        $value = trim((string)$value);
        if ($value === '' || !is_numeric($value)) {
            return null;
        }
        $amount = round((float)$value, 2);
        return ($amount >= 0 && $amount <= self::MAX_REGISTRATION_AMOUNT) ? $amount : null;
    }

    /**
     * Validate the commission fields of the settings form. Only fields that were submitted are returned,
     * so saving other settings never changes these. Returns ['ok' => bool, 'values' => [...], 'error' => ?string].
     */
    public static function validateSettingsInput(array $post): array
    {
        $values = [];
        if (array_key_exists(self::KEY_CONTRIBUTION_ENABLED, $post)) {
            $values[self::KEY_CONTRIBUTION_ENABLED] = self::normalizeEnabledValue($post[self::KEY_CONTRIBUTION_ENABLED]) ? '1' : '0';
        }
        if (array_key_exists(self::KEY_REGISTRATION_AMOUNT, $post)) {
            $amount = self::normalizeAmount($post[self::KEY_REGISTRATION_AMOUNT]);
            if ($amount === null) {
                return ['ok' => false, 'values' => [], 'error' => 'Registration commission must be a number between 0 and ' . number_format(self::MAX_REGISTRATION_AMOUNT) . '.'];
            }
            $values[self::KEY_REGISTRATION_AMOUNT] = (string)$amount;
        }
        return ['ok' => true, 'values' => $values, 'error' => null];
    }

    public static function isContributionCommissionEnabled(?SettingsService $settings = null): bool
    {
        try {
            $settings = $settings ?: new SettingsService();
            return self::normalizeEnabledValue($settings->get(self::KEY_CONTRIBUTION_ENABLED, '0'));
        } catch (Throwable $e) {
            error_log('Commission policy read failed (contribution commission stays OFF): ' . $e->getMessage());
            return false; // fail closed
        }
    }

    public static function registrationCommissionAmount(?SettingsService $settings = null): float
    {
        try {
            $settings = $settings ?: new SettingsService();
            $amount = self::normalizeAmount($settings->get(self::KEY_REGISTRATION_AMOUNT, (string)self::DEFAULT_REGISTRATION_AMOUNT));
            return $amount ?? self::DEFAULT_REGISTRATION_AMOUNT;
        } catch (Throwable $e) {
            error_log('Commission policy read failed (using default registration commission): ' . $e->getMessage());
            return self::DEFAULT_REGISTRATION_AMOUNT;
        }
    }
}
