<?php
/**
 * Membership Pricing Service
 *
 * Centralizes tier resolution and contribution calculations.
 */
class MembershipPricingService
{
    const TIER_INDIVIDUAL = 'individual';
    const TIER_FAMILY = 'family';
    const TIER_EXTENDED_FAMILY_1 = 'extended_family_1';
    const TIER_EXTENDED_FAMILY_2 = 'extended_family_2';
    const TIER_EXECUTIVE = 'executive';

    /**
     * Canonical tier metadata used by forms/cards/help text.
     *
     * @return array
     */
    public static function getTierDefinitions()
    {
        return [
            self::TIER_INDIVIDUAL => [
                'label' => 'Individual Tier',
                'description' => 'Principal member only',
                'coverage' => ['Principal member only'],
                'pricing' => [
                    'below_70' => 100,
                    '71_80' => 350,
                    '81_90' => 450,
                    '91_100' => 650
                ]
            ],
            self::TIER_FAMILY => [
                'label' => 'Family Tier',
                'description' => 'Principal + spouse',
                'coverage' => ['Husband', 'Wife'],
                'pricing' => [
                    'flat' => 150
                ]
            ],
            self::TIER_EXTENDED_FAMILY_1 => [
                'label' => 'Extended Family Tier 1',
                'description' => 'Couple + children + parents',
                'coverage' => ['Husband', 'Wife', 'Children (below 18)', 'Parents'],
                'pricing' => [
                    'below_70' => 250,
                    '71_80' => 350,
                    '81_90' => 450,
                    '91_100' => 650
                ]
            ],
            self::TIER_EXTENDED_FAMILY_2 => [
                'label' => 'Extended Family Tier 2',
                'description' => 'Couple + children + parents + in-laws',
                'coverage' => ['Husband', 'Wife', 'Children (below 18)', 'Parents', 'In-laws'],
                'pricing' => [
                    'below_70' => 300,
                    '71_80' => 400,
                    '81_90' => 550,
                    '91_100' => 650
                ]
            ],
            self::TIER_EXECUTIVE => [
                'label' => 'Executive Tier',
                'description' => 'Premium individual coverage',
                'coverage' => ['Principal member only'],
                'pricing' => [
                    'below_70' => 300,
                    'above_70' => 500
                ]
            ]
        ];
    }

    /**
     * Canonical tier ordering used by upgrade/navigation flows.
     *
     * @return array
     */
    public static function getTierOrder()
    {
        return [
            self::TIER_INDIVIDUAL,
            self::TIER_FAMILY,
            self::TIER_EXTENDED_FAMILY_1,
            self::TIER_EXTENDED_FAMILY_2,
            self::TIER_EXECUTIVE
        ];
    }

    /**
     * Normalize a raw package/category to canonical tiers.
     *
     * @param string|null $value
     * @param array $packageDefinition
     * @return string
     */
    public static function normalizeTier($value, array $packageDefinition = [])
    {
        $raw = strtolower((string)$value);
        $category = strtolower((string)($packageDefinition['category'] ?? ''));

        if ($raw === self::TIER_EXECUTIVE || $category === self::TIER_EXECUTIVE || strpos($raw, 'executive') !== false) {
            return self::TIER_EXECUTIVE;
        }

        if ($raw === self::TIER_EXTENDED_FAMILY_2
            || $category === self::TIER_EXTENDED_FAMILY_2
            || strpos($raw, 'inlaws') !== false
            || strpos($raw, 'maximum_family') !== false) {
            return self::TIER_EXTENDED_FAMILY_2;
        }

        if ($raw === self::TIER_EXTENDED_FAMILY_1
            || $category === self::TIER_EXTENDED_FAMILY_1
            || strpos($raw, 'parents') !== false
            || strpos($raw, 'extended_family') !== false) {
            return self::TIER_EXTENDED_FAMILY_1;
        }

        if ($raw === self::TIER_FAMILY
            || $raw === 'couple'
            || $category === self::TIER_FAMILY
            || strpos($raw, 'couple') !== false) {
            return self::TIER_FAMILY;
        }

        return self::TIER_INDIVIDUAL;
    }

    /**
     * Infer tier from household composition unless premium was selected.
     *
     * @param array $composition
     * @return string
     */
    public static function resolveTierFromComposition(array $composition)
    {
        if (!empty($composition['premium_selected'])) {
            return self::TIER_EXECUTIVE;
        }

        $hasSpouse = !empty($composition['spouse_count']);
        $hasChildren = !empty($composition['children_count']);
        $hasParents = !empty($composition['parents_count']);
        $hasInlaws = !empty($composition['inlaws_count']);

        if ($hasSpouse && $hasChildren && $hasParents && $hasInlaws) {
            return self::TIER_EXTENDED_FAMILY_2;
        }

        if ($hasSpouse && $hasChildren && $hasParents) {
            return self::TIER_EXTENDED_FAMILY_1;
        }

        if ($hasSpouse) {
            return self::TIER_FAMILY;
        }

        return self::TIER_INDIVIDUAL;
    }

    /**
     * Calculate monthly contribution from tier + ages + corporate rule.
     *
     * @param array $input
     * @return array
     */
    public static function calculateMonthlyContribution(array $input)
    {
        $tier = self::normalizeTier($input['tier'] ?? ($input['package'] ?? self::TIER_INDIVIDUAL), $input['package_definition'] ?? []);

        $principalAge = (int)($input['principal_age'] ?? 0);
        $parentsAnchorAge = (int)($input['parents_anchor_age'] ?? 0);
        $inlawsAnchorAge = (int)($input['inlaws_anchor_age'] ?? 0);
        $corporateCouples = max(0, (int)($input['corporate_couple_count'] ?? 0));

        $anchorAge = self::resolveAnchorAgeForTier($tier, $principalAge, $parentsAnchorAge, $inlawsAnchorAge);
        $ageBand = self::resolveAgeBand($anchorAge);

        $basePrice = self::resolveBasePrice($tier, $ageBand);
        $corporateAddon = $corporateCouples * $basePrice;

        return [
            'tier' => $tier,
            'age_band' => $ageBand,
            'anchor_age' => $anchorAge,
            'base_price' => $basePrice,
            'corporate_couple_count' => $corporateCouples,
            'corporate_addon' => $corporateAddon,
            'total_price' => $basePrice + $corporateAddon
        ];
    }

    /**
     * @param string $tier
     * @param int $principalAge
     * @param int $parentsAnchorAge
     * @param int $inlawsAnchorAge
     * @return int
     */
    public static function resolveAnchorAgeForTier($tier, $principalAge, $parentsAnchorAge, $inlawsAnchorAge)
    {
        if ($tier === self::TIER_EXTENDED_FAMILY_2) {
            return max(0, $parentsAnchorAge, $inlawsAnchorAge, $principalAge);
        }

        if ($tier === self::TIER_EXTENDED_FAMILY_1) {
            return max(0, $parentsAnchorAge, $principalAge);
        }

        return max(0, $principalAge);
    }

    /**
     * @param int $age
     * @return string
     */
    public static function resolveAgeBand($age)
    {
        if ($age >= 91) {
            return '91_100';
        }

        if ($age >= 81) {
            return '81_90';
        }

        if ($age >= 71) {
            return '71_80';
        }

        return 'below_70';
    }

    /**
     * @param string $tier
     * @param string $ageBand
     * @return int
     */
    public static function resolveBasePrice($tier, $ageBand)
    {
        $tiers = self::getTierDefinitions();
        $pricing = $tiers[$tier]['pricing'] ?? [];

        if ($tier === self::TIER_FAMILY) {
            return (int)($pricing['flat'] ?? 150);
        }

        if ($tier === self::TIER_EXECUTIVE) {
            return (int)(($ageBand === 'below_70') ? ($pricing['below_70'] ?? 300) : ($pricing['above_70'] ?? 500));
        }

        return (int)($pricing[$ageBand] ?? 0);
    }
}
