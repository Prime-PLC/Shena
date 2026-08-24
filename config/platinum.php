<?php
/**
 * SHENA Platinum add-on pricing and policy configuration.
 * Prices are monthly Platinum-inclusive contributions in KES.
 */

return [
    'annual_days_limit' => 20,
    'maturity_months' => [
        'under_60' => 4,
        '60_and_above' => 7,
    ],
    'prices' => [
        'individual' => [
            'under_70' => 300,
            '71_80' => 550,
            '81_90' => 650,
            '91_100' => 850,
        ],
        'couple' => [
            'under_70' => 350,
        ],
        'couple_children' => [
            'under_70' => 400,
        ],
        'couple_children_parents' => [
            'under_70' => 450,
            '71_80' => 550,
            '81_90' => 650,
            '91_100' => 850,
        ],
        'couple_children_parents_inlaws' => [
            'under_70' => 500,
            '71_80' => 600,
            '81_90' => 750,
            '91_100' => 850,
        ],
        'executive' => [
            'under_70' => 500,
            '70_and_above' => 700,
        ],
    ],
];
