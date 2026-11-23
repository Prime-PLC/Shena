<?php

// Agents portal: registrations by month, traceable commission totals, duplicate-commission
// protection, and access control (an agent only ever sees their own members).

$root = dirname(__DIR__);
$failures = [];
$expect = static function (bool $ok, string $message) use (&$failures): void {
    if (!$ok) {
        $failures[] = $message;
    }
};

if (!class_exists('BaseModel')) {
    class BaseModel { protected $db; protected $table; }
}
require_once $root . '/app/models/Agent.php';

// --- Month filter parsing ---
$expect(Agent::normalizeMonth('2025-12') === '2025-12', 'A valid YYYY-MM month must be accepted.');
foreach (['', '2025-13', '2025-00', '2025-1', 'December', '2025-12-01', "2025-12'; DROP"] as $bad) {
    $expect(Agent::normalizeMonth($bad) === null, 'Invalid month "' . $bad . '" must mean all months.');
}

// --- Registrations by month, including month boundaries and duplicates ---
$members = [
    ['id' => 1, 'created_at' => '2025-11-30 23:59:59', 'status' => 'active'],
    ['id' => 2, 'created_at' => '2025-12-01 00:00:00', 'status' => 'active'],
    ['id' => 3, 'created_at' => '2025-12-31 23:59:59', 'status' => 'pending_payment'],
    ['id' => 4, 'created_at' => '2026-01-01 00:00:00', 'status' => 'active'],
    ['id' => 2, 'created_at' => '2025-12-01 00:00:00', 'status' => 'active'], // duplicate row for the same member
];
$summary = Agent::groupRegistrationsByMonth($members);
$byMonth = array_column($summary, null, 'month');
$expect(array_column($summary, 'month') === ['2026-01', '2025-12', '2025-11'], 'Months must be listed newest first.');
$expect(($byMonth['2025-11']['count'] ?? 0) === 1 && ($byMonth['2025-12']['count'] ?? 0) === 2 && ($byMonth['2026-01']['count'] ?? 0) === 1,
    'Month boundaries and duplicate rows must be handled (a member is counted once, in its registration month).');
$expect(($byMonth['2025-12']['active'] ?? 0) === 1 && ($byMonth['2025-12']['pending'] ?? 0) === 1, 'Monthly active/pending counts must be accurate.');
$expect(array_sum(array_column($summary, 'count')) === 4, 'Monthly totals must add up to the number of distinct members.');
$filtered = Agent::filterMembersByMonth($members, '2025-12');
$expect(count($filtered) === 3 && count(Agent::filterMembersByMonth($members, null)) === 5, 'Month filter must narrow the list; no filter keeps all.');

// --- Commission breakdown by payment month ---
$commissions = [
    ['commission_amount' => 100, 'amount' => 1000, 'status' => 'paid',     'payment_date' => '2025-11-28 10:00:00', 'created_at' => '2025-12-02 09:00:00'],
    ['commission_amount' => 100, 'amount' => 1000, 'status' => 'pending',  'payment_date' => '2025-12-03 10:00:00', 'created_at' => '2025-12-03 10:05:00'],
    ['commission_amount' => 50,  'amount' => 500,  'status' => 'approved', 'payment_date' => null,                  'created_at' => '2025-12-09 08:00:00'],
    ['commission_amount' => 999, 'amount' => 9990, 'status' => 'cancelled','payment_date' => '2025-12-04 10:00:00', 'created_at' => '2025-12-04 10:00:00'],
];
$monthly = array_column(Agent::summarizeCommissionsByMonth($commissions), null, 'month');
$expect(($monthly['2025-11']['commission_amount'] ?? 0) == 100 && ($monthly['2025-11']['paid'] ?? 0) == 100,
    'A commission belongs to the month its payment was made, not the month it was recorded.');
$expect(($monthly['2025-12']['commission_amount'] ?? 0) == 150 && ($monthly['2025-12']['pending'] ?? 0) == 100 && ($monthly['2025-12']['approved'] ?? 0) == 50,
    'December must total pending + approved, falling back to the record date when there is no payment date.');
$expect(array_sum(array_column($monthly, 'commission_amount')) == 250, 'Cancelled commissions must be excluded and monthly totals must reconcile.');

// --- Duplicate commission protection (idempotent per payment and type) ---
class FakeStmt {
    public function __construct(private $row) {}
    public function fetch() { return $this->row; }
    public function fetchAll() { return []; }
    public function fetchColumn() { return 0; }
}
class FakeDb {
    public array $inserts = [];
    public $existing = null;
    public function query($sql, $params = []) {
        if (stripos(ltrim($sql), 'SELECT id FROM agent_commissions') === 0) { return new FakeStmt($this->existing); }
        if (stripos(ltrim($sql), 'INSERT INTO agent_commissions') === 0) { $this->inserts[] = $params; return new FakeStmt(true); }
        return new FakeStmt(false);
    }
    public function getConnection() { return new class { public function lastInsertId() { return 99; } }; }
}
$agent = (new ReflectionClass('Agent'))->newInstanceWithoutConstructor();
$db = new FakeDb();
$prop = new ReflectionProperty('BaseModel', 'db');
$prop->setAccessible(true);
$prop->setValue($agent, $db);
$row = ['agent_id' => 5, 'member_id' => 9, 'payment_id' => 77, 'commission_type' => 'monthly_contribution',
        'amount' => 1000, 'commission_rate' => 10, 'commission_amount' => 100, 'status' => 'pending'];
try {
    $agent->recordCommission($row);
    $expect(count($db->inserts) === 1, 'The first commission for a payment must be recorded.');
    $db->existing = ['id' => 41];
    $again = $agent->recordCommission($row);
    $expect(count($db->inserts) === 1 && $again == 41, 'Recording a commission for the same payment again must not create a duplicate.');
} catch (Throwable $e) {
    $failures[] = 'recordCommission test could not run: ' . $e->getMessage();
}

// --- Access control and wiring (contract) ---
$controller = file_get_contents($root . '/app/controllers/AgentDashboardController.php');
$memberModel = file_get_contents($root . '/app/models/Member.php');
$expect(preg_match('/function __construct\(\).*?requireAuth\(\);\s*\$this->requireAgent\(\);/s', $controller) === 1, 'Every agent portal action must require an authenticated agent.');
$expect(preg_match('/function memberDetails.*?\$member\[\'agent_id\'\] != \$agent\[\'id\'\]/s', $controller) === 1, 'Member details must verify the member belongs to the agent.');
$expect(preg_match('/function getMembersByAgent.*?WHERE m\.agent_id = :agent_id/s', $memberModel) === 1, 'Agent member lists must be scoped to the agent.');
$expect(strpos($controller, "Agent::normalizeMonth(\$_GET['month']") !== false && strpos($controller, "'monthly_summary'") !== false, 'Controller must wire the month filter and monthly summary.');
$expect(strpos($controller, 'We could not load your members right now') !== false, 'Members page must show an error state when loading fails.');
$payments = file_get_contents($root . '/app/services/PaymentService.php');
$expect(strpos($payments, 'commissionExistsForPayment') !== false, 'Commission generation must skip payments that already have a commission.');
$members_view = file_get_contents($root . '/resources/views/agent/members.php');
$comm_view = file_get_contents($root . '/resources/views/agent/commissions.php');
foreach (['Registrations by month', 'No members registered in this month', 'Figures as of', 'name="month"'] as $needle) {
    $expect(strpos($members_view, $needle) !== false, 'Members view must include: ' . $needle);
}
foreach (['Commission by Payment Month', 'Figures as of'] as $needle) {
    $expect(strpos($comm_view, $needle) !== false, 'Commissions view must include: ' . $needle);
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}
echo "Agent portal monthly report checks passed.\n";
