<?php

/** Exact Basic package mapping; owner DOB is used only for maturity. */
class PlatinumPricingService
{
    public static function canonicalPackageKey(string $key): string
    {
        $key = trim($key);
        return $key === 'couple_children_parents_70_80' ? 'couple_children_parents_71_80' : $key;
    }

    public static function resolvePackageKey(array $record): string
    {
        global $membership_packages;
        $key = self::canonicalPackageKey((string)($record['package_key'] ?? ''));
        $legacy = self::canonicalPackageKey((string)($record['package'] ?? ''));
        if ($key !== '' && isset($membership_packages[$legacy]) && $key !== $legacy) return '';
        return $key !== '' ? $key : (isset($membership_packages[$legacy]) ? $legacy : '');
    }

    public static function quoteToken(int $memberId, string $type, ?int $personId, array $quote): string
    {
        return hash('sha256', json_encode([$memberId, $type, $personId, $quote]));
    }

    public function packageAmount(string $packageKey): ?float
    {
        global $membership_packages, $platinum_config;
        $packageKey = self::canonicalPackageKey($packageKey);
        $package = $membership_packages[$packageKey] ?? null;
        if (!$package) return null;
        $type = $package['coverage_type'] === 'principal_only' ? 'individual' : $package['coverage_type'];
        // The assigned package already identifies the price band. Never reselect it from owner DOB.
        $age = (int)$package['age_min'];
        $band = $age <= 70 ? 'under_70' : ($type === 'executive' ? '70_and_above'
            : ($age <= 80 ? '71_80' : ($age <= 90 ? '81_90' : '91_100')));
        $amount = $platinum_config['prices'][$type][$band] ?? null;
        return $amount === null ? null : (float)$amount;
    }

    public function quote(string $packageKey, ?string $ownerDob): ?array
    {
        global $membership_packages, $platinum_config;
        $packageKey = self::canonicalPackageKey($packageKey);
        $amount = $this->packageAmount($packageKey);
        if ($amount === null || !$ownerDob) return null;
        try { $dob = DateTimeImmutable::createFromFormat('!Y-m-d', $ownerDob); }
        catch (Throwable $e) { return null; }
        $today = new DateTimeImmutable('today');
        if (!$dob || $dob->format('Y-m-d') !== $ownerDob || $dob > $today) return null;
        $age = $dob->diff($today)->y;
        if ($age < 18 || $age > 100) return null;
        $package = $membership_packages[$packageKey];
        return [
            'amount' => $amount,
            'age' => $age,
            'maturity_months' => $age < 60 ? (int)($platinum_config['maturity_months']['under_60'] ?? 4)
                : (int)($platinum_config['maturity_months']['60_and_above'] ?? 7),
            'package_key' => $packageKey,
            'package_name' => (string)$package['name'],
            'coverage_type' => (string)$package['coverage_type'],
        ];
    }
}
