<?php

$root = dirname(__DIR__);

$checks = [
    [
        'file' => 'app/controllers/BulkEmailController.php',
        'mustContain' => [
            '$campaigns = $this->bulkEmailService->getAllCampaigns',
            '$message = trim($input[\'message\'] ?? $input[\'body\'] ?? \'\');',
            '$scheduleType = $input[\'schedule_type\'] ?? null;',
            '$this->jsonError',
            '$this->readRequestData',
            '$this->view(\'admin.campaign-details\'',
        ],
    ],
    [
        'file' => 'app/controllers/BulkSmsController.php',
        'mustContain' => [
            '$campaigns = $this->bulkSmsService->getAllCampaigns',
            '$queue_items = $this->bulkSmsService->getQueueItems',
            '$scheduleType = $_POST[\'schedule_type\'] ?? null;',
            '$targetAudience = $this->normalizeTargetAudience($rawTargetAudience);',
            '$this->view(\'admin.campaign-details\'',
        ],
    ],
    [
        'file' => 'resources/views/admin/email-campaigns.php',
        'mustContain' => [
            'name="message"',
            '<option value="active">Active Members Only</option>',
            '<option value="defaulted">Payment Defaulters</option>',
        ],
        'mustNotContain' => [
            'name="body"',
            'value="active_only"',
            'value="defaulters"',
        ],
    ],
    [
        'file' => 'resources/views/admin/sms-campaigns.php',
        'mustContain' => [
            '<option value="active">Active Members Only</option>',
            '<option value="payment_unpaid_current">Not Paid</option>',
            '<option value="payment_defaulted">Defaulted</option>',
            '<option value="agent_all">All Agents</option>',
        ],
        'mustNotContain' => [
            'value="active_only"',
            'value="defaulters"',
        ],
    ],
    [
        'file' => 'resources/views/admin/campaign-details.php',
        'mustContain' => [
            'Recipient Delivery Log',
            'provider_message_id',
            'status-pill',
        ],
    ],
];

$failed = false;

foreach ($checks as $check) {
    $path = $root . '/' . $check['file'];
    $contents = file_get_contents($path);

    foreach ($check['mustContain'] as $needle) {
        if (strpos($contents, $needle) === false) {
            fwrite(STDERR, $check['file'] . " is missing expected marker: {$needle}\n");
            $failed = true;
        }
    }

    foreach (($check['mustNotContain'] ?? []) as $needle) {
        if (strpos($contents, $needle) !== false) {
            fwrite(STDERR, $check['file'] . " still contains unsupported marker: {$needle}\n");
            $failed = true;
        }
    }
}

exit($failed ? 1 : 0);
