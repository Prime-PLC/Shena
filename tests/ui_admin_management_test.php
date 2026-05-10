<?php

$root = dirname(__DIR__);

$checks = [
    [
        'file' => 'resources/views/admin/members.php',
        'mustContain' => [
            'openMemberModal',
            'memberDetailModal',
            'management-table',
            'action-group',
        ],
        'mustNotContain' => [
            "onclick=\"window.location.href='/admin/members/view/",
        ],
    ],
    [
        'file' => 'resources/views/admin/agents.php',
        'mustContain' => [
            'openAgentModal',
            'agentDetailModal',
            'management-table',
            'action-group',
        ],
        'mustNotContain' => [
            "onclick=\"window.location.href='/admin/agents/view/",
        ],
    ],
];

$failed = false;

foreach ($checks as $check) {
    $path = $root . '/' . $check['file'];
    $contents = file_get_contents($path);

    foreach ($check['mustContain'] as $needle) {
        if (strpos($contents, $needle) === false) {
            fwrite(STDERR, $check['file'] . " is missing expected UI marker: {$needle}\n");
            $failed = true;
        }
    }

    foreach ($check['mustNotContain'] as $needle) {
        if (strpos($contents, $needle) !== false) {
            fwrite(STDERR, $check['file'] . " still contains legacy navigation marker: {$needle}\n");
            $failed = true;
        }
    }
}

exit($failed ? 1 : 0);
