<?php

class MemberNumberHelper
{
    private const PREFIX = 'SH-';

    /**
     * Generate canonical member number format: SHNNNNNN.
     *
     * @return string
     */
    public static function generateCanonical()
    {
        $attempts = 0;

        do {
            $memberNumber = self::PREFIX . random_int(100000, 999999);
            $attempts++;

            if ($attempts > 20) {
                throw new Exception('Unable to generate a unique member number.');
            }
        } while (self::exists($memberNumber));

        return $memberNumber;
    }

    public static function format(int $year, int $sequence): string
    {
        return self::PREFIX . str_pad((string)$sequence, 6, '0', STR_PAD_LEFT);
    }

    private static function exists(string $memberNumber): bool
    {
        $db = Database::getInstance();
        $existing = $db->fetch(
            'SELECT id FROM members WHERE member_number = :member_number LIMIT 1',
            ['member_number' => $memberNumber]
        );

        return !empty($existing);
    }

    /**
     * Parse a member number to extract components
     * 
     * @param string $memberNumber
     * @return array|null
     */
    public static function parse($memberNumber)
    {
        if (preg_match('/^(SH-)(\d{6,})$/', $memberNumber, $matches)) {
            return [
                'prefix' => $matches[1],
                'year' => null,
                'sequence' => $matches[2],
                'full' => $memberNumber
            ];
        }

        if (preg_match('/^(SHENA|SHA)-(\d{4})-([A-Z0-9]{4,})$/', $memberNumber, $matches)) {
            return [
                'prefix' => $matches[1],
                'year' => $matches[2],
                'sequence' => $matches[3],
                'full' => $memberNumber
            ];
        }

        return null;
    }

    /**
     * Format member number consistently (normalize old format to new)
     * 
     * @param string $memberNumber
     * @return string
     */
    public static function normalize($memberNumber)
    {
        $parsed = self::parse($memberNumber);
        if ($parsed) {
            return self::PREFIX . strtoupper($parsed['sequence']);
        }
        return $memberNumber;
    }
}
