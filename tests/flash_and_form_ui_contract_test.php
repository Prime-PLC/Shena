<?php

$root = dirname(__DIR__);
$failed = false;

$checks = [
    [
        'file' => 'app/helpers/functions.php',
        'mustContain' => [
            'function collectFlashMessages',
            "'success_message' => 'success'",
            "'error_message' => 'error'",
            "'flash_message'",
            'function renderFlashMessagesScript',
            '__shenaFlashDispatched',
        ],
    ],
    [
        'file' => 'resources/views/layouts/admin-header.php',
        'mustContain' => [
            'renderFlashMessagesScript',
        ],
    ],
    [
        'file' => 'resources/views/layouts/member-header.php',
        'mustContain' => [
            'renderFlashMessagesScript',
        ],
    ],
    [
        'file' => 'resources/views/layouts/agent-header.php',
        'mustContain' => [
            'renderFlashMessagesScript',
        ],
        'mustNotContain' => [
            "isset(\$_SESSION['flash_message'])",
        ],
    ],
    [
        'file' => 'resources/views/layouts/header.php',
        'mustContain' => [
            'renderFlashMessagesScript',
        ],
        'mustNotContain' => [
            "getFlashMessage('success')",
        ],
    ],
    [
        'file' => 'resources/views/admin/member-edit.php',
        'mustNotContain' => [
            "isset(\$_SESSION['success_message'])",
            "isset(\$_SESSION['error_message'])",
        ],
    ],
    [
        'file' => 'resources/views/admin/agent-edit.php',
        'mustNotContain' => [
            "isset(\$_SESSION['success_message'])",
            "isset(\$_SESSION['error_message'])",
        ],
    ],
    [
        'file' => 'resources/views/admin/members.php',
        'mustContain' => [
            '.entity-modal .form-input',
            '.entity-modal .form-select',
            '.member-quick-panel .modal-content',
            'grid-template-columns: repeat(auto-fit, minmax(240px, 1fr))',
        ],
    ],
];

foreach ($checks as $check) {
    $path = $root . '/' . $check['file'];
    if (!file_exists($path)) {
        fwrite(STDERR, $check['file'] . " does not exist\n");
        $failed = true;
        continue;
    }

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
