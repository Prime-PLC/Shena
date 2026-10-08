<?php
require_once __DIR__ . '/PlatinumPricingService.php';
require_once __DIR__ . '/MembershipPricingService.php';

/** A product choice is authoritative; invalid Platinum never falls back to Basic. */
class RegistrationPlanService
{
    public static function validate(string $tier, string $key, string $dob): array
    {
        global $membership_packages;
        if (!in_array($tier, ['0', '1'], true)) throw new InvalidArgumentException('Choose SHENA Basic or SHENA Platinum.');
        $package = $membership_packages[$key] ?? null;
        if (!$package || !empty($package['legacy_alias'])) throw new InvalidArgumentException('Choose a valid package for the selected product.');
        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $dob);
        $today = new DateTimeImmutable('today');
        if (!$birth || $birth->format('Y-m-d') !== $dob || $birth > $today) throw new InvalidArgumentException('Enter a valid date of birth.');
        $age = $birth->diff($today)->y;
        if ($age < 18 || $age > 100) throw new InvalidArgumentException('Registration age must be between 18 and 100.');
        // Individual/executive bands describe the owner. Family bands can describe parents/in-laws.
        if (in_array($package['coverage_type'], ['principal_only', 'executive'], true)
            && ($age < $package['age_min'] || $age > $package['age_max'])) {
            throw new InvalidArgumentException('Choose a package matching the member age.');
        }
        $quote = $tier === '1' ? (new PlatinumPricingService())->quote($key, $dob) : null;
        if ($tier === '1' && !$quote) throw new InvalidArgumentException('Platinum is unavailable for this package. Choose another package.');
        return ['tier'=>$tier === '1' ? 'Platinum' : 'Basic', 'amount'=>$quote['amount'] ?? (float)$package['monthly_contribution'], 'quote'=>$quote];
    }

    /** Called inside the registration transaction, after corporate rows have IDs. */
    public function apply(int $memberId, string $tier, string $key, string $dob): void
    {
        $db = Database::getInstance();
        $principal = self::validate($tier, $key, $dob);
        $groups = $db->fetchAll("SELECT * FROM member_corporate_members WHERE member_id = :id AND status = 'active'", ['id'=>$memberId]);
        // Corporate members always start on Basic. Only a separate admin conversion
        // may enroll a corporate group in Platinum, regardless of the principal tier.
        foreach ($groups as $group) {
            $package = $GLOBALS['membership_packages'][$group['package_key']] ?? null;
            if (!$package || !empty($package['legacy_alias'])) throw new InvalidArgumentException('Choose a valid Basic package for each corporate member.');
            $groupDob = (string)($group['date_of_birth'] ?? '');
            if ($groupDob !== '') self::validate('0', $group['package_key'], $groupDob);
        }
        $quote = $principal['quote'];
        if ($quote) {
            $db->insert('platinum_coverages', [
                'member_id'=>$memberId, 'covered_person_type'=>'principal',
                'covered_person_id'=>null, 'status'=>'pending_approval',
                'registration_selected'=>1, 'package_key'=>$quote['package_key'],
                'package_name'=>$quote['package_name'], 'monthly_contribution'=>$quote['amount'],
                'maturity_months'=>$quote['maturity_months'], 'requested_at'=>date('Y-m-d H:i:s')
            ]);
        }
    }
}
