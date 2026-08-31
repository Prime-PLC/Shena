<?php

/**
 * Resolves a single Platinum add-on price for a Basic coverage group.
 * Dependants are included by the group's selected Basic package; they are
 * never individually priced.
 */
class PlatinumPricingService
{
    public function quote(string $packageKey, ?string $ownerDob): ?array
    {
        global $membership_packages, $platinum_config;

        $package = $membership_packages[$packageKey] ?? null;
        if (!$package || !$ownerDob) {
            return null;
        }

        try {
            $age = (new DateTimeImmutable($ownerDob))->diff(new DateTimeImmutable('today'))->y;
        } catch (Throwable $exception) {
            return null;
        }
        $minimumAge = isset($package['age_min']) ? (int)$package['age_min'] : null;
        $maximumAge = isset($package['age_max']) ? (int)$package['age_max'] : null;
        if (($minimumAge !== null && $age < $minimumAge) || ($maximumAge !== null && $age > $maximumAge)) {
            return null;
        }

        $coverageType = (string) ($package['coverage_type'] ?? 'principal_only');
        $priceKey = $coverageType === 'principal_only' ? 'individual' : $coverageType;
        $prices = $platinum_config['prices'][$priceKey] ?? [];
        $ageBand = $this->ageBand($age, $priceKey);
        $amount = $ageBand === null ? null : ($prices[$ageBand] ?? null);

        if ($amount === null) {
            return null;
        }

        return [
            'amount' => (float) $amount,
            'age' => $age,
            'maturity_months' => $age < 60
                ? (int) ($platinum_config['maturity_months']['under_60'] ?? 4)
                : (int) ($platinum_config['maturity_months']['60_and_above'] ?? 7),
            'package_key' => $packageKey,
            'package_name' => (string) ($package['name'] ?? $packageKey),
            'coverage_type' => $coverageType,
        ];
    }

    private function ageBand(int $age, string $priceKey): ?string
    {
        if ($priceKey === 'executive') {
            return $age < 70 ? 'under_70' : ($age <= 100 ? '70_and_above' : null);
        }
        if ($age < 70) return 'under_70';
        if ($age <= 80) return '71_80';
        if ($age <= 90) return '81_90';
        return $age <= 100 ? '91_100' : null;
    }
}
