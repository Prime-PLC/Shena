<?php
/**
 * Diagnostic integration harness: php tests/platinum_conversion_edge_cases.php
 * Uses the real admin handler and pricing service with in-memory persistence.
 * No application bootstrap, credentials, real database, or SMS delivery.
 * Exit 1 means a desired safety/data-integrity expectation is not met.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
date_default_timezone_set('Africa/Nairobi');
$root = dirname(__DIR__);

// Inject a strict in-memory singleton; loading Database does not connect.
class ConversionDatabaseFixture
{

    public array $rows = [];
    public array $writes = [];
    public ?array $member = null;
    public bool $failAudit = false;
    private bool $transaction = false;
    private array $snapshot = [];
    public function getConnection() { return $this; }
    public function beginTransaction() { $this->snapshot = $this->rows; $this->transaction = true; }
    public function commit() { $this->transaction = false; }
    public function inTransaction() { return $this->transaction; }
    public function rollBack() { $this->rows = $this->snapshot; $this->writes = []; $this->transaction = false; }
    public function fetchAll($sql, $params) {
        if (strpos($sql, 'FROM platinum_coverages') === false) throw new LogicException('Unexpected query');
        return array_values(array_filter(array_reverse($this->rows), static function($row) use ($params) {
            return (int)$row['member_id'] === (int)$params['member_id'] && $row['covered_person_type'] === $params['type']
                && ($row['covered_person_id'] === null ? $params['person_id'] === null : (int)$row['covered_person_id'] === (int)$params['person_id']);
        }));
    }

    public function fetch($sql, $params)
    {
        if (strpos($sql, 'SELECT id FROM members') === 0) return $this->member;
        if (strpos($sql, 'SELECT id FROM member_corporate_members') === 0) return ['id' => $params['id']];
        if (strpos($sql, 'FROM platinum_coverages') === false) {
            throw new LogicException('Unexpected database query: ' . $sql);
        }
        if (isset($params['id'])) {
            foreach ($this->rows as $row) if ((int)$row['id'] === (int)$params['id'] && (int)$row['member_id'] === (int)$params['member_id']) return $row;
            return null;
        }
        foreach (array_reverse($this->rows) as $row) {
            if ((int)$row['member_id'] === (int)$params['member_id']
                && $row['covered_person_type'] === $params['type']
                && (($row['covered_person_id'] === null && $params['person_id'] === null)
                    || ($row['covered_person_id'] !== null && $params['person_id'] !== null
                        && (int)$row['covered_person_id'] === (int)$params['person_id']))) return $row;
        }
        return null;
    }
    public function insert($table, $fields)
    {
        if ($table === 'activity_logs') { if ($this->failAudit) throw new LogicException('Synthetic audit failure'); return 1; }
        if ($table !== 'platinum_coverages') throw new LogicException('Unexpected write');
        $fields['id'] = count($this->rows) + 1;
        $this->rows[] = $fields;
        $this->writes[] = $fields;
        return $fields['id'];
    }
    public function update($table, $fields, $where, $params)
    {
        if ($table !== 'platinum_coverages' || $where !== 'id = :id') throw new LogicException('Unexpected update');
        foreach ($this->rows as &$row) {
            if ($row['id'] === $params['id']) {
                $row = array_replace($row, $fields);
                $this->writes[] = $row;
                return true;
            }
        }
        throw new LogicException('Missing update target');
    }
}
require_once $root . '/app/core/BaseController.php';
require_once $root . '/app/core/BaseModel.php';
require_once $root . '/app/controllers/AdminController.php';
$membership_packages = require $root . '/config/packages.php';
$platinum_config = require $root . '/config/platinum.php';

class ConversionRedirect extends RuntimeException {}
class ConversionMemberFixture
{
    public function __construct(public ?array $record) {}
    public function getMemberById($id) { return $this->record; }
    // Stops before the notification branch: no SMS service is instantiated.
    public function getMemberWithUser($id) { return null; }
}
class ConversionCorporateFixture
{
    public function __construct(public ?array $record) {}
    public function find($id) { return $this->record && (int)$this->record['id'] === (int)$id ? $this->record : null; }
}
class ConversionControllerFixture extends AdminController
{
    public function __construct($db, $member, $corporate)
    {
        $this->db = $db;
        foreach (['memberModel', 'paymentModel', 'claimModel', 'agentModel', 'payoutRequestModel',
            'corporateMemberModel', 'beneficiaryModel', 'paymentStatusService'] as $name) {
            $property = new ReflectionProperty(AdminController::class, $name);
            $property->setValue($this, match ($name) {
                'memberModel' => new ConversionMemberFixture($member),
                'corporateMemberModel' => new ConversionCorporateFixture($corporate),
                default => new stdClass(),
            });
        }
    }
    protected function redirect($url) { throw new ConversionRedirect($url); }
}

function dobAtAge(int $age): string { return (new DateTimeImmutable('today'))->modify("-{$age} years")->format('Y-m-d'); }
function memberFixture(array $overrides = []): array
{
    return array_replace(['id' => 101, 'package_key' => 'individual_below_70', 'package' => 'individual',
        'date_of_birth' => dobAtAge(40), 'monthly_contribution' => '100.00', 'status' => 'active'], $overrides);
}
$cases = [];
function addCase(string $name, ?array $member, ?string $expectedKey, ?float $amount, array $options = []): void
{
    global $cases;
    $cases[] = compact('name', 'member', 'expectedKey', 'amount', 'options');
}

// Independent expected tariff table: do not derive expected amounts from the service.
$tariffs = [
    'individual_below_70' => [18,70,300], 'individual_71_80' => [71,80,550],
    'individual_81_90' => [81,90,650], 'individual_91_100' => [91,100,850],
    'couple_below_70' => [18,70,350], 'couple_children_below_70' => [18,70,400],
    'couple_children_parents_below_70' => [18,70,450],
    'couple_children_parents_71_80' => [71,80,550],
    'couple_children_parents_81_90' => [81,90,650],
    'couple_children_parents_91_100' => [91,100,850],
    'couple_children_parents_inlaws_below_70' => [18,70,500],
    'couple_children_parents_inlaws_71_80' => [71,80,600],
    'couple_children_parents_inlaws_81_90' => [81,90,750],
    'couple_children_parents_inlaws_91_100' => [91,100,850],
    'executive_below_70' => [18,70,500], 'executive_above_70' => [71,100,700],
];
foreach ($tariffs as $key => [$min, $max, $amount]) {
    foreach (array_unique([$min - 1, $min, $max, $max + 1]) as $age) {
        $valid = $age >= 18 && $age <= 100; // Package band is authoritative; DOB only sets maturity.
        $record = memberFixture(['package_key' => $key, 'date_of_birth' => dobAtAge($age)]);
        addCase("principal {$key} age {$age}", $record, $valid ? $key : null, $valid ? $amount : null);
        $corporate = array_replace($record, ['id' => 201, 'member_id' => 101]);
        addCase("corporate {$key} age {$age}", memberFixture(), $valid ? $key : null, $valid ? $amount : null,
            ['corporate' => $corporate, 'post' => ['covered_person_type' => 'corporate_member', 'covered_person_id' => '201']]);
    }
}
foreach ([null, '', ' ', chr(0), '0000-00-00', 'not-a-date', (date('Y') - 40) . '-02-30', '1986-13-01', '40 years ago',
    (new DateTimeImmutable('today'))->modify('+40 years')->format('Y-m-d')] as $dob) {
    addCase('reject invalid DOB ' . var_export($dob, true), memberFixture(['date_of_birth' => $dob]), null, null);
}
foreach ([59,60,69] as $age) {
    addCase("maturity boundary {$age}", memberFixture(['date_of_birth' => dobAtAge($age)]), 'individual_below_70', 300,
        ['maturity' => $age < 60 ? 4 : 7]);
}
addCase('missing DOB column', array_diff_key(memberFixture(), ['date_of_birth' => 1]), null, null);
addCase('legacy dob column only requires review', array_replace(array_diff_key(memberFixture(), ['date_of_birth' => 1]), ['dob' => dobAtAge(40)]), null, null);
foreach ([null, ''] as $key) {
    addCase('legacy exact package fallback ' . var_export($key, true), memberFixture(['package_key' => $key, 'package' => 'couple_children_below_70']), 'couple_children_below_70', 400);
}
addCase('missing package_key exact fallback', array_diff_key(memberFixture(['package' => 'couple_children_below_70']), ['package_key' => 1]), 'couple_children_below_70', 400);
foreach (['individual', 'family', 'medical', 'unknown', '350', ' Individual_Below_70 '] as $key) {
    addCase("unresolved key requires review: {$key}", memberFixture(['package_key' => $key]), null, null);
}
addCase('conflicting exact package fields require review', memberFixture(['package' => 'couple_children_below_70']), null, null);
addCase('cached display name is replaced by canonical package name', memberFixture(['package_name' => 'Couple & Children Below 70 Years']), 'individual_below_70', 300);
foreach ([null, 0, '350.00', '9999.99'] as $storedAmount) {
    addCase('stored amount must not select package ' . var_export($storedAmount, true), memberFixture(['monthly_contribution' => $storedAmount]), 'individual_below_70', 300);
}
addCase('missing member', null, null, null);
addCase('dependent cannot convert separately', memberFixture(), null, null, ['post' => ['covered_person_type' => 'dependent', 'covered_person_id' => 1]]);
addCase('unknown group type', memberFixture(), null, null, ['post' => ['covered_person_type' => 'unknown']]);
addCase('corporate record missing', memberFixture(), null, null, ['post' => ['covered_person_type' => 'corporate_member', 'covered_person_id' => 201]]);
addCase('corporate belongs to another account', memberFixture(), null, null, ['corporate' => ['id' => 201, 'member_id' => 999], 'post' => ['covered_person_type' => 'corporate_member', 'covered_person_id' => 201]]);
addCase('non-admin denied', memberFixture(), null, null, ['role' => 'member']);
addCase('invalid CSRF denied', memberFixture(), null, null, ['post' => ['csrf_token' => 'wrong']]);
foreach (['suspended', 'inactive', 'pending'] as $status) {
    addCase("inactive principal {$status} requires explicit override", memberFixture(['status' => $status]), null, null);
}
$corporate = ['id' => 201, 'member_id' => 101, 'package_key' => 'couple_children_below_70', 'date_of_birth' => dobAtAge(40), 'status' => 'inactive'];
addCase('inactive corporate requires review', memberFixture(), null, null, ['corporate' => $corporate, 'post' => ['covered_person_type' => 'corporate_member', 'covered_person_id' => 201]]);
$old = ['id' => 1, 'member_id' => 101, 'covered_person_type' => 'principal', 'covered_person_id' => null,
    'package_key' => 'individual_below_70', 'package_name' => 'Individual 70 Years and Below', 'status' => 'active', 'monthly_contribution' => 300,
    'effective_from' => '2020-01-01', 'maturity_date' => '2020-05-01'];
addCase('repeat activation must preserve maturity', memberFixture(), 'individual_below_70', 300, ['existing' => [$old], 'preserve_maturity' => true, 'no_writes' => true]);
addCase('duplicate active coverage requires review', memberFixture(), null, null, ['existing' => [$old, array_replace($old, ['id' => 2])]]);
addCase('reactivation must clear previous end date', memberFixture(), 'individual_below_70', 300,
    ['existing' => [array_replace($old, ['status' => 'cancelled', 'coverage_ends_at' => '2021-01-01'])], 'clear_end' => true]);

addCase('backup reproduction: age 52 with parents 70-80 package', memberFixture(['package_key' => 'couple_children_parents_70_80', 'date_of_birth' => dobAtAge(52)]), 'couple_children_parents_71_80', 550);
addCase('backup reproduction: younger owner with inlaws 81-90 package', memberFixture(['package_key' => 'couple_children_parents_inlaws_81_90', 'date_of_birth' => dobAtAge(45)]), 'couple_children_parents_inlaws_81_90', 750);
addCase('trimmed canonical key', memberFixture(['package_key' => 'individual_below_70 ']), 'individual_below_70', 300);
addCase('stale reviewed conversion quote', memberFixture(), null, null, ['post' => ['reviewed_quote' => 'stale']]);
addCase('audit failure rolls back conversion', memberFixture(), null, null, ['fail_audit' => true]);
addCase('return active cover to Basic without SMS', memberFixture(), 'individual_below_70', 300, ['action' => 'revertMemberPlatinumToBasic', 'existing' => [$old], 'expected_status' => 'cancelled', 'post' => ['platinum_coverage_id' => 1]]);
addCase('repeat Basic reversal is a no-op', memberFixture(), 'individual_below_70', 300, ['action' => 'revertMemberPlatinumToBasic', 'existing' => [array_replace($old, ['status' => 'cancelled'])], 'expected_status' => 'cancelled', 'post' => ['platinum_coverage_id' => 1], 'preserve_maturity' => true, 'no_writes' => true]);
addCase('foreign coverage cannot be reversed', memberFixture(), null, null, ['action' => 'revertMemberPlatinumToBasic', 'existing' => [array_replace($old, ['member_id' => 999])], 'post' => ['platinum_coverage_id' => 1]]);
addCase('reversal audit failure rolls back', memberFixture(), null, null, ['action' => 'revertMemberPlatinumToBasic', 'existing' => [$old], 'post' => ['platinum_coverage_id' => 1], 'fail_audit' => true]);
$failures = [];
foreach ($cases as $case) {
    $options = $case['options'];
    $db = new ConversionDatabaseFixture();
    (new ReflectionProperty(Database::class, 'instance'))->setValue(null, $db);
    $db->rows = $options['existing'] ?? [];
    $db->member = $case['member'];
    $db->failAudit = !empty($options['fail_audit']);
    $_SESSION = ['user_id' => 901, 'user_role' => $options['role'] ?? 'super_admin', 'csrf_token' => 'fixture-token'];
    $_POST = array_replace(['csrf_token' => 'fixture-token', 'covered_person_type' => 'principal'], $options['post'] ?? []);
    $owner = $_POST['covered_person_type'] === 'corporate_member' ? ($options['corporate'] ?? []) : ($case['member'] ?? []);
    $quote = (new PlatinumPricingService())->quote(PlatinumPricingService::resolvePackageKey($owner), $owner['date_of_birth'] ?? null);
    if ($quote && !array_key_exists('reviewed_quote', $_POST)) $_POST['reviewed_quote'] = PlatinumPricingService::quoteToken(101, $_POST['covered_person_type'], $_POST['covered_person_type'] === 'principal' ? null : (int)($_POST['covered_person_id'] ?? 0), $quote);
    $controller = new ConversionControllerFixture($db, $case['member'], $options['corporate'] ?? null);
    $exception = null;
    try { $action = $options['action'] ?? 'migrateMemberToPlatinum'; $controller->$action(101); }
    catch (ConversionRedirect $e) { /* expected controller termination */ }
    catch (Throwable $e) { $exception = $e->getMessage(); }
    $actual = $db->writes ? end($db->writes) : (!empty($options['preserve_maturity']) ? ($db->rows[0] ?? null) : null);
    $errors = [];
    if ($case['expectedKey'] === null) {
        if ($actual) $errors[] = 'expected rejection; activated ' . $actual['package_key'] . ' at KES ' . $actual['monthly_contribution'];
        if ($exception && $exception !== 'CSRF token mismatch') $errors[] = 'unhandled exception: ' . $exception;
    } else {
        if (!$actual) $errors[] = 'expected activation; ' . ($exception ?? $_SESSION['error'] ?? 'no write');
        elseif ($actual['package_key'] !== $case['expectedKey'] || (float)$actual['monthly_contribution'] !== (float)$case['amount']) {
            $errors[] = 'wrong package/amount: ' . json_encode($actual);
        }
        if ($actual && $actual['status'] !== ($options['expected_status'] ?? 'active')) $errors[] = 'coverage not active';
        if ($actual && isset($options['maturity']) && $actual['maturity_months'] !== $options['maturity']) $errors[] = 'wrong maturity months';
        if ($actual && !empty($options['preserve_maturity']) && $actual['maturity_date'] !== $old['maturity_date']) $errors[] = 'existing maturity reset';
        if ($actual && !empty($options['clear_end']) && !empty($actual['coverage_ends_at'])) $errors[] = 'old coverage end date retained';
    }
    if ($actual && $case['expectedKey'] !== null) {
        $expectedType = $_POST['covered_person_type'];
        $expectedId = $expectedType === 'principal' ? null : (int)$_POST['covered_person_id'];
        if ($actual['covered_person_type'] !== $expectedType || $actual['covered_person_id'] !== $expectedId
            || (int)$actual['member_id'] !== 101) $errors[] = 'wrong coverage owner persisted';
        if ($actual['package_name'] !== $membership_packages[$case['expectedKey']]['name']) $errors[] = 'wrong package name persisted';
    }
    if (!empty($options['no_writes']) && $db->writes) $errors[] = 'repeated action wrote again';
    if ($db->inTransaction()) $errors[] = 'transaction left open';
    if ($errors) $failures[] = ['case' => $case['name'], 'errors' => $errors];
    if (in_array('--verbose', $argv, true) || $errors) echo ($errors ? 'FAIL ' : 'PASS ') . $case['name'] . ($errors ? ': ' . implode('; ', $errors) : '') . PHP_EOL;
}
echo count($cases) . ' cases, ' . (count($cases) - count($failures)) . ' passed, ' . count($failures) . ' failed.' . PHP_EOL;
echo 'Synthetic storage only; real SQL, browser preview, billing totals and SMS transport are not exercised.' . PHP_EOL;
exit($failures ? 1 : 0);
