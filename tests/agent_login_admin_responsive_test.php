<?php

$root = dirname(__DIR__);

$checks = [
    [
        'file' => 'app/models/User.php',
        'mustContain' => [
            'findByAgentCredential',
            'INNER JOIN agents a ON u.id = a.user_id',
            'a.national_id = :credential',
            'a.agent_number = :agent_number',
            '$user = $this->findByAgentCredential($credential);',
        ],
    ],
    [
        'file' => 'app/controllers/AuthController.php',
        'mustContain' => [
            '$user = $this->userModel->findByAnyCredential($idInput);',
            "'No account found for that identifier. Please check and try again.'",
        ],
        'mustNotContain' => [
            '$member = $this->memberModel->findByNationalId($idInput);',
        ],
    ],
    [
        'file' => 'app/controllers/AdminController.php',
        'mustContain' => [
            '$loginUser = $this->userModel->findByAnyCredential($username);',
            "in_array(\$loginUser['role'], ['super_admin', 'manager', 'agent'], true)",
            "if (\$loginUser['role'] === 'agent') {",
            "header('Location: /agent/dashboard');",
        ],
        'mustNotContain' => [
            "if (\$admin && in_array(\$admin['role'], ['super_admin', 'manager'])",
        ],
    ],
    [
        'file' => 'resources/views/auth/login.php',
        'mustContain' => [
            'National ID, Agent Number, or Member Number',
            'Agent Login',
            'href="/login"',
        ],
        'mustNotContain' => [
            '<a href="/admin/login" class="footer-link">
                        <i class="fas fa-user-shield"></i>',
        ],
    ],
    [
        'file' => 'resources/views/admin/login.php',
        'mustContain' => [
            'min-height: 100dvh',
            'overflow-y: auto',
            'clamp(',
            '@media (max-width: 576px)',
            '@media (max-height: 720px)',
        ],
        'mustNotContain' => [
            'overflow: hidden;',
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
