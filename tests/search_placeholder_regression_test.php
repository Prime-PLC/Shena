<?php

$root = dirname(__DIR__);
$failed = false;

$files = [
    'app/models/Member.php',
    'app/models/Dependent.php',
];

foreach ($files as $file) {
    $path = $root . '/' . $file;
    $contents = file_get_contents($path);
    preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*/', $contents, $matches);
    $counts = array_count_values($matches[0]);
    foreach ($counts as $placeholder => $count) {
        if ($count > 1 && in_array($placeholder, [':search'], true)) {
            fwrite(STDERR, "{$file} repeats {$placeholder}; native PDO prepares require unique placeholders\n");
            $failed = true;
        }
    }
}

$memberContents = file_get_contents($root . '/app/models/Member.php');
foreach ([
    ':search_member_number',
    ':search_id_number',
    ':search_first_name',
    ':search_last_name',
    ':search_email',
    ':search_phone',
    ':search_full_name',
] as $needle) {
    if (strpos($memberContents, $needle) === false) {
        fwrite(STDERR, "Member search is missing {$needle}\n");
        $failed = true;
    }
}

$dependentContents = file_get_contents($root . '/app/models/Dependent.php');
foreach ([':dependent_name_search', ':dependent_id_search', ':dependent_birth_cert_search'] as $needle) {
    if (strpos($dependentContents, $needle) === false) {
        fwrite(STDERR, "Dependent search is missing {$needle}\n");
        $failed = true;
    }
}

exit($failed ? 1 : 0);
