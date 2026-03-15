<?php
/**
 * Canonical package definitions for Shena
 * Returned as an associative array keyed by package slug.
 */

return [
    'individual_below_70' => [
        'name' => 'Individual Below 70 Years',
        'description' => 'Individual coverage for members below 70 years',
        'monthly_contribution' => 100,
        'age_min' => 18,
        'age_max' => 69,
        'category' => 'individual',
        'coverage_type' => 'principal_only',
        'services' => 'all'
    ],
    'individual_71_80' => [
        'name' => 'Individual 71-80 Years',
        'description' => 'Individual coverage for members aged 71-80 years',
        'monthly_contribution' => 350,
        'age_min' => 71,
        'age_max' => 80,
        'category' => 'individual',
        'coverage_type' => 'principal_only',
        'services' => 'all'
    ],
    'individual_81_90' => [
        'name' => 'Individual 81-90 Years',
        'description' => 'Individual coverage for members aged 81-90 years',
        'monthly_contribution' => 450,
        'age_min' => 81,
        'age_max' => 90,
        'category' => 'individual',
        'coverage_type' => 'principal_only',
        'services' => 'all'
    ],
    'individual_91_100' => [
        'name' => 'Individual 91-100 Years',
        'description' => 'Individual coverage for members aged 91-100 years',
        'monthly_contribution' => 650,
        'age_min' => 91,
        'age_max' => 100,
        'category' => 'individual',
        'coverage_type' => 'principal_only',
        'services' => 'all'
    ],
    'couple_below_70' => [
        'name' => 'Couple Below 70 Years',
        'description' => 'Coverage for couples below 70 years',
        'monthly_contribution' => 150,
        'age_min' => 18,
        'age_max' => 69,
        'category' => 'family',
        'coverage_type' => 'couple',
        'services' => 'all'
    ],
    'couple_children_below_70' => [
        'name' => 'Couple & Children Below 70 Years',
        'description' => 'Coverage for couple and children below 18 years',
        'monthly_contribution' => 200,
        'age_min' => 18,
        'age_max' => 69,
        'category' => 'family',
        'coverage_type' => 'couple_children',
        'max_children' => 10,
        'services' => 'all'
    ],
    'couple_children_parents_below_70' => [
        'name' => 'Couple, Children & Parents Below 70 Years',
        'description' => 'Coverage for couple, children and parents below 70 years',
        'monthly_contribution' => 250,
        'age_min' => 18,
        'age_max' => 69,
        'category' => 'extended_family_1',
        'coverage_type' => 'couple_children_parents',
        'max_children' => 10,
        'max_parents' => 4,
        'services' => 'all'
    ],
    'couple_children_parents_inlaws_below_70' => [
        'name' => 'Couple, Children, Parents & In-laws Below 70 Years',
        'description' => 'Coverage for couple, children, parents and in-laws below 70 years',
        'monthly_contribution' => 300,
        'age_min' => 18,
        'age_max' => 69,
        'category' => 'extended_family_2',
        'coverage_type' => 'couple_children_parents_inlaws',
        'max_children' => 10,
        'max_parents' => 4,
        'max_inlaws' => 4,
        'services' => 'all'
    ],
    'couple_children_parents_70_80' => [
        'name' => 'Couple, Children & Parents 70-80 Years',
        'description' => 'Coverage for couple, children and parents aged 70-80 years',
        'monthly_contribution' => 350,
        'age_min' => 70,
        'age_max' => 80,
        'category' => 'extended_family_1',
        'coverage_type' => 'couple_children_parents',
        'max_children' => 10,
        'max_parents' => 4,
        'services' => 'all'
    ],
    'couple_children_parents_inlaws_71_80' => [
        'name' => 'Couple, Children, Parents & In-laws 71-80 Years',
        'description' => 'Coverage for couple, children, parents and in-laws aged 71-80 years',
        'monthly_contribution' => 400,
        'age_min' => 71,
        'age_max' => 80,
        'category' => 'extended_family_2',
        'coverage_type' => 'couple_children_parents_inlaws',
        'max_children' => 10,
        'max_parents' => 4,
        'max_inlaws' => 4,
        'services' => 'all'
    ],
    'couple_children_parents_81_90' => [
        'name' => 'Couple, Children & Parents 81-90 Years',
        'description' => 'Coverage for couple, children and parents aged 81-90 years',
        'monthly_contribution' => 450,
        'age_min' => 81,
        'age_max' => 90,
        'category' => 'extended_family_1',
        'coverage_type' => 'couple_children_parents',
        'max_children' => 10,
        'max_parents' => 4,
        'services' => 'all'
    ],
    'couple_children_parents_inlaws_81_90' => [
        'name' => 'Couple, Children, Parents & In-laws 81-90 Years',
        'description' => 'Coverage for couple, children, parents and in-laws aged 81-90 years',
        'monthly_contribution' => 550,
        'age_min' => 81,
        'age_max' => 90,
        'category' => 'extended_family_2',
        'coverage_type' => 'couple_children_parents_inlaws',
        'max_children' => 10,
        'max_parents' => 4,
        'max_inlaws' => 4,
        'services' => 'all'
    ],
    'couple_children_parents_91_100' => [
        'name' => 'Couple, Children & Parents 91-100 Years',
        'description' => 'Coverage for couple, children and parents aged 91-100 years',
        'monthly_contribution' => 650,
        'age_min' => 91,
        'age_max' => 100,
        'category' => 'extended_family_1',
        'coverage_type' => 'couple_children_parents',
        'max_children' => 10,
        'max_parents' => 4,
        'services' => 'all'
    ],
    'couple_children_parents_inlaws_91_100' => [
        'name' => 'Couple, Children, Parents & In-laws 91-100 Years',
        'description' => 'Coverage for couple, children, parents and in-laws aged 91-100 years',
        'monthly_contribution' => 650,
        'age_min' => 91,
        'age_max' => 100,
        'category' => 'extended_family_2',
        'coverage_type' => 'couple_children_parents_inlaws',
        'max_children' => 10,
        'max_parents' => 4,
        'max_inlaws' => 4,
        'services' => 'all'
    ],
    'executive_below_70' => [
        'name' => 'Executive Package Below 70 Years',
        'description' => 'Premium executive coverage for individuals below 70 years with enhanced services',
        'monthly_contribution' => 300,
        'age_min' => 18,
        'age_max' => 69,
        'category' => 'executive',
        'coverage_type' => 'executive',
        'premium_features' => true,
        'services' => 'all_premium'
    ],
    'executive_above_70' => [
        'name' => 'Executive Package Above 70 Years',
        'description' => 'Premium executive coverage for individuals above 70 years with enhanced services',
        'monthly_contribution' => 500,
        'age_min' => 70,
        'age_max' => 100,
        'category' => 'executive',
        'coverage_type' => 'executive',
        'premium_features' => true,
        'services' => 'all_premium'
    ]
];
