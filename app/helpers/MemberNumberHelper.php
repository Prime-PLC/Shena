<?php

class MemberNumberHelper
{
    /**
     * Generate canonical member number format: SHA-YYYY-####
     *
     * @return string
     */
    public static function generateCanonical()
    {
        $db = Database::getInstance();
        $year = date('Y');
        $prefix = 'SHA-' . $year . '-';

        $sql = 'SELECT member_number FROM members WHERE member_number LIKE :pattern ORDER BY member_number DESC LIMIT 1';
        $last = $db->fetch($sql, ['pattern' => $prefix . '%']);

        $sequence = 1;
        if (!empty($last['member_number']) && preg_match('/^SHA-' . $year . '-(\d{4})$/', $last['member_number'], $matches)) {
            $sequence = ((int)$matches[1]) + 1;
        }

        return $prefix . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
