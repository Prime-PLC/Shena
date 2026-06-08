<?php

$root = dirname(__DIR__);

$checks = [
    [
        'file' => 'app/services/BulkSmsService.php',
        'mustContain' => [
            'private function buildRecipientQuery',
            'LEFT JOIN notification_preferences np ON u.id = np.user_id',
            "COALESCE(np.sms_enabled, 1) = 1",
            'recipient_type, recipient_value, status, error_message',
            'invalid_phone',
            'replacePlaceholders',
            "'member_name'",
            "'amount_due'",
            'recalculateCampaignCounts',
            "SUM(CASE WHEN bmr.status = 'submitted' THEN 1 ELSE 0 END) AS submitted_count",
            "SUM(CASE WHEN bmr.status IN ('sent', 'delivered') THEN 1 ELSE 0 END) AS sent_count",
            'provider_response',
        ],
        'mustNotContain' => [
            'm.member_number as name',
            'INNER JOIN notification_preferences np ON u.id = np.user_id',
            'SET sent_count = sent_count + ?',
        ],
    ],
    [
        'file' => 'app/services/BulkEmailService.php',
        'mustContain' => [
            'private function buildRecipientQuery',
            'filter_var($email, FILTER_VALIDATE_EMAIL)',
            'invalid_email',
            'replacePlaceholders',
            "'member_name'",
            "'package'",
            "'amount_due'",
            'recalculateCampaignCounts',
            'SUM(CASE WHEN bmr.status = \'sent\' THEN 1 ELSE 0 END) AS sent_count',
            'provider_response',
        ],
        'mustNotContain' => [
            '$this->db->quote',
            'SET sent_count = sent_count + ?',
        ],
    ],
    [
        'file' => 'resources/views/admin/email-campaigns.php',
        'mustContain' => [
            'id="custom-filters-panel"',
            "this.value === 'custom'",
            'name="filter_status"',
            'name="filter_package"',
            'name="filter_joined_after"',
            'name="filter_joined_before"',
            '{member_name}',
            '{amount_due}',
        ],
    ],
    [
        'file' => 'resources/views/admin/campaign-details.php',
        'mustContain' => [
            '$skipped =',
            'Skipped',
            'provider_response',
            'member_number',
            'recipient_type',
        ],
    ],
];

$failed = false;

foreach ($checks as $check) {
    $path = $root . '/' . $check['file'];
    $contents = file_get_contents($path);

    foreach ($check['mustContain'] ?? [] as $needle) {
        if (strpos($contents, $needle) === false) {
            fwrite(STDERR, $check['file'] . " is missing expected marker: {$needle}\n");
            $failed = true;
        }
    }

    foreach ($check['mustNotContain'] ?? [] as $needle) {
        if (strpos($contents, $needle) !== false) {
            fwrite(STDERR, $check['file'] . " still contains forbidden marker: {$needle}\n");
            $failed = true;
        }
    }
}

exit($failed ? 1 : 0);
