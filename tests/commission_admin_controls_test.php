<?php

// Agent commission rule: one commission per registered member; commission on member
// contributions stays OFF until a system administrator switches it on.

$root = dirname(__DIR__);
$failures = [];
$expect = static function (bool $ok, string $message) use (&$failures): void {
    if (!$ok) {
        $failures[] = $message;
    }
};

require_once $root . '/app/services/CommissionPolicy.php';

// --- Switch parsing: only an explicit "on" value turns contribution commission on ---
foreach (['1', 'true', 'on', 'yes', ' TRUE '] as $on) {
    $expect(CommissionPolicy::normalizeEnabledValue($on) === true, "'$on' should switch it on.");
}
foreach (['0', '', null, 'off', 'false', 'no', 'maybe', '2'] as $off) {
    $expect(CommissionPolicy::normalizeEnabledValue($off) === false, "'" . var_export($off, true) . "' must leave it OFF.");
}

// --- Registration amount: 0..100000, numeric only ---
$expect(CommissionPolicy::normalizeAmount('200') === 200.0 && CommissionPolicy::normalizeAmount('250.5') === 250.5 && CommissionPolicy::normalizeAmount('0') === 0.0, 'Valid amounts must be accepted (0 allowed).');
foreach (['', 'abc', '-1', '100000.01', '1e9', null] as $bad) {
    $expect(CommissionPolicy::normalizeAmount($bad) === null, "Amount '" . var_export($bad, true) . "' must be rejected.");
}

// --- Settings form validation: only submitted fields change; bad amount saves nothing ---
$r = CommissionPolicy::validateSettingsInput(['app_name' => 'X']);
$expect($r['ok'] && $r['values'] === [], 'Saving unrelated settings must not touch commission settings.');
$r = CommissionPolicy::validateSettingsInput(['agent_contribution_commission_enabled' => '1', 'agent_registration_commission_amount' => '300']);
$expect($r['ok'] && $r['values'] === ['agent_contribution_commission_enabled' => '1', 'agent_registration_commission_amount' => '300'], 'Valid commission settings must be returned for saving.');
$r = CommissionPolicy::validateSettingsInput(['agent_contribution_commission_enabled' => '0']);
$expect($r['ok'] && $r['values']['agent_contribution_commission_enabled'] === '0', 'Switching it off must save 0.');
$r = CommissionPolicy::validateSettingsInput(['agent_contribution_commission_enabled' => '1', 'agent_registration_commission_amount' => 'abc']);
$expect(!$r['ok'] && $r['values'] === [] && $r['error'] !== null, 'An invalid amount must reject the whole commission change.');

// --- Reading the settings: default OFF / KES 200, and fail closed on errors ---
class FakeSettings extends SettingsService {
    public array $values = [];
    public bool $throw = false;
    public function __construct() {}
    public function get($key, $default = null) {
        if ($this->throw) { throw new RuntimeException('db down'); }
        return $this->values[$key] ?? $default;
    }
}
$fake = new FakeSettings();
$expect(CommissionPolicy::isContributionCommissionEnabled($fake) === false, 'With no setting stored, contribution commission must be OFF.');
$expect(CommissionPolicy::registrationCommissionAmount($fake) === 200.0, 'With no setting stored, registration commission defaults to KES 200.');
$fake->values = ['agent_contribution_commission_enabled' => '1', 'agent_registration_commission_amount' => '350'];
$expect(CommissionPolicy::isContributionCommissionEnabled($fake) === true && CommissionPolicy::registrationCommissionAmount($fake) === 350.0, 'Stored administrator settings must be used.');
$fake->values = ['agent_registration_commission_amount' => 'garbage'];
$expect(CommissionPolicy::registrationCommissionAmount($fake) === 200.0, 'A corrupt amount must fall back to the default.');
$fake->throw = true;
$expect(CommissionPolicy::isContributionCommissionEnabled($fake) === false, 'If settings cannot be read, contribution commission must stay OFF.');
$expect(CommissionPolicy::registrationCommissionAmount($fake) === 200.0, 'If settings cannot be read, the default registration commission applies.');

// --- Wiring (contract) ---
$payments = file_get_contents($root . '/app/services/PaymentService.php');
$controller = file_get_contents($root . '/app/controllers/AdminController.php');
$settingsView = file_get_contents($root . '/resources/views/admin/settings.php');
$agentView = file_get_contents($root . '/resources/views/agent/commissions.php');

$expect(preg_match('/function recordCommissionForPayment\([^)]*\)\s*\{\s*\/\/[^\n]*\n\s*if \(!CommissionPolicy::isContributionCommissionEnabled\(\)\) \{\s*return;/', $payments) === 1,
    'recordCommissionForPayment must check the administrator switch before doing anything.');
$expect(preg_match('/if \(\$paymentType === \'monthly\'\) \{\s*\$this->recordCommissionForPayment/', $payments) === 1, 'The monthly commission call must be present (and is guarded by the switch).');
$expect(strpos($payments, 'CommissionPolicy::registrationCommissionAmount()') !== false, 'Registration commission must use the administrator amount.');
$expect(preg_match("/'commission_type' => 'registration',\s*'amount' => 200/", $payments) === 0, 'The registration commission must not be hard-coded to 200.');
$expect(strpos($controller, 'CommissionPolicy::validateSettingsInput($_POST)') !== false, 'Saving settings must validate the commission fields.');
$expect(strpos($controller, "'agent_contribution_commission_enabled' => 0") !== false, 'The settings page must default contribution commission to OFF.');
foreach (['id="commission"', 'name="agent_contribution_commission_enabled" value="0"', 'type="checkbox" id="agent_contribution_commission_enabled"', 'name="agent_registration_commission_amount"'] as $needle) {
    $expect(strpos($settingsView, $needle) !== false, 'Settings page must include: ' . $needle);
}
$expect(strpos($agentView, 'Registration commission') !== false, 'Agent commissions page must label registration commissions.');

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}
echo "Commission admin controls checks passed.\n";
