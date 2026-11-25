<?php

// Admin registration can attribute a member to an active agent or to the Office.
// Office registrations carry no agent (and therefore no agent reporting/commission).

$root = dirname(__DIR__);
$failures = [];
$expect = static function (bool $ok, string $message) use (&$failures): void {
    if (!$ok) {
        $failures[] = $message;
    }
};

if (!class_exists('BaseModel')) {
    class BaseModel {}
}
require_once $root . '/app/models/Agent.php';

$active = ['id' => 7, 'status' => 'active'];

// Valid agent assignment
$r = Agent::resolveAttribution('7', $active);
$expect($r['ok'] && $r['agent_id'] === 7 && $r['is_office'] === false, 'An active agent ID must resolve to that agent.');

// Office assignment (explicit and empty keep today's behaviour: no agent)
foreach (['office', 'OFFICE', ' Office ', ''] as $value) {
    $r = Agent::resolveAttribution($value, null);
    $expect($r['ok'] && $r['agent_id'] === null && $r['is_office'] === true, 'Office selection "' . $value . '" must resolve to no agent.');
}

// Invalid / unavailable agents are rejected safely
$expect(!Agent::resolveAttribution('999', null)['ok'], 'An unknown agent ID must be rejected.');
$expect(!Agent::resolveAttribution('8', $active)['ok'], 'A mismatched agent row must be rejected.');
$expect(!Agent::resolveAttribution('7', ['id' => 7, 'status' => 'suspended'])['ok'], 'A suspended agent must be rejected.');
$expect(!Agent::resolveAttribution('7', ['id' => 7, 'status' => 'inactive'])['ok'], 'An inactive agent must be rejected.');
foreach (['abc', '-1', '7 OR 1=1', '7.5', '<script>'] as $bad) {
    $expect(!Agent::resolveAttribution($bad, $active)['ok'], 'Malformed selection "' . $bad . '" must be rejected.');
}

// Controller: validates before any database write and only sets agent_id for real agents.
$controller = file_get_contents($root . '/app/controllers/AdminController.php');
$start = strpos($controller, 'public function registerMember()');
$body = substr($controller, $start, 20000);
$posResolve = strpos($body, 'Agent::resolveAttribution');
$posFirstWrite = strpos($body, 'MemberNumberHelper::generateCanonical()');
$expect($posResolve !== false && $posFirstWrite !== false && $posResolve < $posFirstWrite,
    'Agent attribution must be validated before the user/member records are created.');
$expect(strpos($body, "\$_POST['agent_attribution']") !== false, 'Controller must read the agent_attribution selector.');
$expect(preg_match('/if \(\$attributedAgentId !== null\) \{\s*\$memberData\[\'agent_id\'\] = \$attributedAgentId;/', $body) === 1,
    'agent_id must only be written to the member when an agent (not Office) was selected.');
$expect(strpos($body, "'agents' => \$this->agentModel->getAssignableAgents()") !== false, 'The form must be given the eligible agents.');

// Eligible agents: active only.
$agentModel = file_get_contents($root . '/app/models/Agent.php');
$expect(preg_match("/function getAssignableAgents.*?WHERE a\\.status = 'active'/s", $agentModel) === 1, 'Only active agents may be offered for attribution.');

// Commission attribution: payments only create commissions for members that have an agent.
$payments = file_get_contents($root . '/app/services/PaymentService.php');
$expect(preg_match('/function recordCommissionForPayment.*?empty\(\$member\[\'agent_id\'\]\)\)\s*\{\s*return;/s', $payments) === 1,
    'Commission generation must skip members without an agent (Office registrations).');

// Form: Office option, searchable, accessible, selection preserved, old free-text field gone.
$view = file_get_contents($root . '/resources/views/admin/register-member.php');
foreach (['name="agent_attribution"', 'value="office"', 'id="agentSearch"', 'aria-live="polite"', 'aria-controls="agentAttribution"', "\$old['agent_attribution']"] as $needle) {
    $expect(strpos($view, $needle) !== false, 'Registration form must include ' . $needle);
}
$expect(strpos($view, 'name="agent_number"') === false, 'The unused free-text agent number field must be replaced by the selector.');
$details = file_get_contents($root . '/resources/views/admin/member-details.php');
$expect(strpos($details, 'Office (no agent)') !== false, 'Member details should show Office when there is no agent.');

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}
echo "Admin registration agent attribution checks passed.\n";
