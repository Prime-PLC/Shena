<?php

// Regression: dependant date-of-birth must align with the child category
// (below 18), newborns are accepted, future dates are rejected, and the
// member portal calendar enforces the same bounds as the server.

$root = dirname(__DIR__);
$failures = [];

// Lightweight stand-in so Member can be exercised without a database.
if (!class_exists('BaseModel')) {
    class BaseModel {}
}
require_once $root . '/app/services/MembershipPricingService.php';
require_once $root . '/app/models/Member.php';

$model = (new ReflectionClass('Member'))->newInstanceWithoutConstructor();
$member = ['id' => 1, 'status' => 'active', 'package_key' => 'couple_children_below_70'];

$check = static function (string $label, array $dependant, bool $expectAllowed, ?string $messageNeedle = null) use ($model, $member, &$failures): void {
    $result = $model->validateDependentAddition($member, [], $dependant + ['full_name' => 'Test Person']);
    if (!empty($result['allowed']) !== $expectAllowed) {
        $failures[] = $label . ': expected ' . ($expectAllowed ? 'allowed' : 'rejected') . ', got ' . json_encode($result);
        return;
    }
    if ($messageNeedle !== null && stripos((string)$result['message'], $messageNeedle) === false) {
        $failures[] = $label . ': message should mention "' . $messageNeedle . '", got "' . $result['message'] . '"';
    }
};

$today = new DateTimeImmutable('today');
$fmt = static fn(DateTimeImmutable $d): string => $d->format('Y-m-d');

$check('newborn child (age 0) is allowed', ['relationship' => 'child', 'date_of_birth' => $fmt($today->modify('-2 months'))], true);
$check('child born today is allowed', ['relationship' => 'child', 'date_of_birth' => $fmt($today)], true);
$check('17 year old child is allowed', ['relationship' => 'child', 'date_of_birth' => $fmt($today->modify('-17 years -11 months'))], true);
$check('child turning 18 today is rejected', ['relationship' => 'child', 'date_of_birth' => $fmt($today->modify('-18 years'))], false, 'below 18');
$check('adult child is rejected', ['relationship' => 'child', 'date_of_birth' => $fmt($today->modify('-25 years'))], false, 'below 18');
$check('future DOB is rejected', ['relationship' => 'child', 'date_of_birth' => $fmt($today->modify('+3 years'))], false, 'future');
$check('future DOB (spouse) is rejected', ['relationship' => 'spouse', 'date_of_birth' => $fmt($today->modify('+1 day'))], false, 'future');
$check('garbage DOB is rejected', ['relationship' => 'child', 'date_of_birth' => 'not-a-date'], false, 'valid');
$check('minor spouse is rejected', ['relationship' => 'spouse', 'date_of_birth' => $fmt($today->modify('-10 years'))], false, '18');
$check('adult spouse is allowed', ['relationship' => 'spouse', 'date_of_birth' => $fmt($today->modify('-30 years'))], true);

// Child slot limits (10 on this plan) still apply.
$existing = [];
for ($i = 0; $i < 10; $i++) {
    $existing[] = ['relationship' => 'child'];
}
$full = $model->validateDependentAddition($member, $existing, [
    'full_name' => 'Eleventh Child', 'relationship' => 'child', 'date_of_birth' => $fmt($today->modify('-5 years')),
]);
if (!empty($full['allowed'])) {
    $failures[] = 'child slot limit must still be enforced';
}

// Contract checks on the member portal and controller.
$view = file_get_contents($root . '/resources/views/member/beneficiaries.php');
$controller = file_get_contents($root . '/app/controllers/MemberController.php');

foreach (['updateDobConstraints', 'addDateOfBirth', 'editDateOfBirth', "rel.value === 'child'", 'childMin'] as $needle) {
    if (strpos($view, $needle) === false) {
        $failures[] = 'Member beneficiaries view must constrain the DOB calendar by category (' . $needle . ').';
    }
}
foreach (['under 21', 'children under 21'] as $stale) {
    if (stripos($view, $stale) !== false) {
        $failures[] = 'Member beneficiaries view must not advertise a children-under-21 rule; policy is below 18.';
    }
}
if (!preg_match('/function updateBeneficiary\(\).*?validateDependentAddition/s', $controller)) {
    $failures[] = 'Member updateBeneficiary must run validateDependentAddition so edits cannot bypass child/DOB rules.';
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo "Dependant DOB / child category regression checks passed.\n";
