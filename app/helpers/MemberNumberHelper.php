<?php

class MemberNumberHelper
{
    private const PREFIX = 'SHENA';

    /**
     * Generate canonical member number format: SHENA-YYYY-NNNNNN.
     * When called inside a transaction, the SELECT ... FOR UPDATE lock is held
     * until the caller inserts the member and commits.
     *
     * @return string
     */
    public static function generateCanonical()
    {
        $db = Database::getInstance();
        $year = date('Y');
        $prefix = self::PREFIX . '-' . $year . '-';
        $connection = $db->getConnection();
        $startedTransaction = false;

        try {
            if (!$connection->inTransaction()) {
                $connection->beginTransaction();
                $startedTransaction = true;
            }

            $sql = 'SELECT member_number FROM members WHERE member_number LIKE :pattern ORDER BY member_number DESC LIMIT 1 FOR UPDATE';
            $last = $db->fetch($sql, ['pattern' => $prefix . '%']);

            $sequence = 1;
            if (!empty($last['member_number']) && preg_match('/^' . self::PREFIX . '-' . $year . '-(\d{6,})$/', $last['member_number'], $matches)) {
                $sequence = ((int)$matches[1]) + 1;
            }

            $memberNumber = self::format((int)$year, $sequence);

            if ($startedTransaction) {
                $connection->commit();
            }

            return $memberNumber;
        } catch (Exception $e) {
            if ($startedTransaction && $connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $e;
        }
    }

    public static function format(int $year, int $sequence): string
    {
        return self::PREFIX . '-' . $year . '-' . str_pad((string)$sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Parse a member number to extract components
     * 
     * @param string $memberNumber
     * @return array|null
     */
    public static function parse($memberNumber)
    {
        if (preg_match('/^(SHENA|SHA)-(\d{4})-(\d{4,})$/', $memberNumber, $matches)) {
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
            $seq = str_pad($parsed['sequence'], 6, '0', STR_PAD_LEFT);
            return self::PREFIX . '-' . $parsed['year'] . '-' . $seq;
        }
        return $memberNumber;
    }
}
