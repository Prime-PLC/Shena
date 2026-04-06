<?php
$f = file_get_contents(__DIR__ . '/../database/shena_production.sql');
$checks = [
    'double_VIEW (want 0)'       => substr_count($f, 'VIEW VIEW'),
    '0900_collation (want 0)'    => substr_count($f, '0900_ai_ci'),
    'DEFINER (want 0)'           => substr_count($f, 'DEFINER'),
    'checkout col in payments (want 1)' => preg_match_all('/`checkout_request_id`\s+varchar/', $f),
    'OR REPLACE VIEW lines (want 9)' => substr_count($f, "CREATE OR REPLACE VIEW `"),
    'support_tickets (want 1+)'  => substr_count($f, 'CREATE TABLE `support_tickets`'),
    'stk_push_logs (want 1+)'    => substr_count($f, 'CREATE TABLE `mpesa_stk_push_logs`'),
    'shortcode 4163987 (want 1)' => substr_count($f, '4163987'),
    'domain shenacompanion (want 2+)' => substr_count($f, 'shenacompanion.co.ke'),
];
foreach ($checks as $label => $count) {
    echo str_pad($label, 45) . ": $count\n";
}
