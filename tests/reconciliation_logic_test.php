<?php
/**
 * Behavioural tests for payment reconciliation integrity and the AI matching
 * helpers. No database or network needed: the DB/model layer is stubbed.
 *
 * Run: php tests/reconciliation_logic_test.php   (exit code 0 = pass)
 */

// ---------------------------------------------------------------- test harness
$GLOBALS['__failures'] = 0;
$GLOBALS['__passes'] = 0;

function check($condition, $label)
{
    if ($condition) {
        $GLOBALS['__passes']++;
        return;
    }
    $GLOBALS['__failures']++;
    fwrite(STDERR, "FAIL: {$label}\n");
}

// ---------------------------------------------------------------- stubs
$tmpRoot = sys_get_temp_dir() . '/shena_recon_test_' . getmypid();
@mkdir($tmpRoot . '/app/services', 0777, true);
file_put_contents($tmpRoot . '/app/services/SmsService.php',
    "<?php class SmsService { public function sendPaymentConfirmationSms(\$p, \$d) { \$GLOBALS['sms'][] = \$p; } }");
define('ROOT_PATH', $tmpRoot);
define('REGISTRATION_FEE', 200);

function formatKenyanPhone($phone)
{
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        return '254' . substr($phone, 1);
    }
    return $phone;
}

class FakePdo
{
    public $inTx = false;
    public $log = [];
    public $failOnCommit = false;
    private $nextId = 100;

    public function inTransaction() { return $this->inTx; }
    public function beginTransaction() { $this->inTx = true; $this->log[] = 'BEGIN'; }
    public function commit() { $this->inTx = false; $this->log[] = 'COMMIT'; }
    public function rollBack() { $this->inTx = false; $this->log[] = 'ROLLBACK'; }
    public function lastInsertId() { return ++$this->nextId; }
}

class Database
{
    public static $instance;
    public $pdo;
    public $sqls = [];
    public $fetchMap = [];   // substring => row|null
    public $failOn = null;   // substring that throws

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
            self::$instance->pdo = new FakePdo();
        }
        return self::$instance;
    }
    public static function reset()
    {
        self::$instance = null;
        return self::getInstance();
    }
    public function getConnection() { return $this->pdo; }
    public function fetch($sql, $params = [])
    {
        foreach ($this->fetchMap as $needle => $row) {
            if (strpos($sql, $needle) !== false) {
                return $row;
            }
        }
        return null;
    }
    public function fetchAll($sql, $params = []) { return []; }
    public function execute($sql, $params = [])
    {
        if ($this->failOn && strpos($sql, $this->failOn) !== false) {
            throw new Exception('Database query failed: simulated');
        }
        $this->sqls[] = [$sql, $params];
        return true;
    }
    public function ran($needle)
    {
        foreach ($this->sqls as [$sql]) {
            if (stripos($sql, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
    public function paramsFor($needle)
    {
        foreach ($this->sqls as [$sql, $params]) {
            if (stripos($sql, $needle) !== false) {
                return $params;
            }
        }
        return null;
    }
}

class Payment
{
    public static $existing = null;
    public static $rows = [];
    public $confirmed = [];
    public function findByReceiptNumber($r) { return self::$existing; }
    public function find($id) { return self::$rows[$id] ?? null; }
    public function confirmPayment($id, $t = null) { $this->confirmed[] = $id; return true; }
    public function activateMemberAfterRegistrationPayment($id) { Member::$calls[] = "register:$id"; }
}

class Member
{
    public static $byId = null;
    public static $byNumber = null;
    public static $byPhone = null;
    public static $rows = [];
    public static $calls = [];
    public static $throwOnApply = false;

    public function findByIdNumber($v) { return self::$byId; }
    public function findByMemberNumber($v) { return self::$byNumber; }
    public function findByPhone($v) { return self::$byPhone; }
    public function find($id) { return self::$rows[$id] ?? null; }
    public function getMemberWithUser($id) { return ['phone' => '254700000001', 'id' => $id]; }
    public function applySuccessfulMonthlyPayment($id, $date = '')
    {
        if (self::$throwOnApply) {
            throw new Exception('coverage update failed');
        }
        self::$calls[] = "monthly:$id";
        return true;
    }
    public function reactivateMember($id) { self::$calls[] = "reactivate:$id"; }
}

function freshWorld()
{
    $db = Database::reset();
    Payment::$existing = null;
    Payment::$rows = [];
    Member::$byId = Member::$byNumber = Member::$byPhone = null;
    Member::$rows = [];
    Member::$calls = [];
    Member::$throwOnApply = false;
    $GLOBALS['sms'] = [];
    return $db;
}

require_once __DIR__ . '/../app/services/PaymentReconciliationService.php';
require_once __DIR__ . '/../app/services/AiMatchingService.php';

$member = ['id' => 7, 'status' => 'active', 'monthly_contribution' => 350, 'first_name' => 'Amina', 'last_name' => 'Otieno'];
$paymentData = function (array $over = []) {
    return array_merge([
        'trans_id' => 'RCP123', 'amount' => 350, 'transaction_date' => '2026-11-18 10:00:00',
        'sender_phone' => '254711111111', 'sender_name' => 'AMINA OTIENO',
        'bill_ref_number' => '12345678', 'callback_id' => 55,
    ], $over);
};

// ================================================================ pure functions
$R = 'PaymentReconciliationService';

check($R::paymentTypeForStatus('inactive') === 'registration', 'inactive -> registration');
check($R::paymentTypeForStatus('defaulted') === 'reactivation', 'defaulted -> reactivation');
check($R::paymentTypeForStatus('active') === 'monthly', 'active -> monthly');
check($R::paymentTypeForStatus('grace_period') === 'monthly', 'grace_period -> monthly');

check($R::evaluateAmount($member, 'monthly', 350)['ok'] === true, 'exact monthly amount ok');
$low = $R::evaluateAmount($member, 'monthly', 10);
check($low['ok'] === false && strpos($low['reason'], 'differs') !== false, 'KES 10 vs 350 rejected with reason');
check($R::evaluateAmount($member, 'monthly', 700)['ok'] === false, 'double payment goes to review, not silently 1 month');
$noContribution = $R::evaluateAmount(['monthly_contribution' => 0], 'monthly', 123);
check($noContribution['ok'] === true && $noContribution['unknown_expected'] === true, 'unknown expected amount is allowed but flagged');
check($R::evaluateAmount($member, 'monthly', 0)['ok'] === false, 'zero amount rejected');
check($R::evaluateAmount($member, 'registration', 200)['ok'] === true, 'registration fee exact ok');
check($R::evaluateAmount($member, 'registration', 550)['ok'] === true, 'registration fee + extra ok');
check($R::evaluateAmount($member, 'registration', 100)['ok'] === false, 'below registration fee rejected');
check($R::evaluateAmount($member, 'reactivation', 999)['ok'] === true, 'reactivation has no fixed rule');

check($R::decideAutoPost('auto_phone', ['ok' => true])['auto_post'] === false, 'phone-only never auto-posts');
check($R::decideAutoPost('auto_id_number', ['ok' => true])['auto_post'] === true, 'ID + good amount auto-posts');
check($R::decideAutoPost('auto_member_number', ['ok' => false, 'reason' => 'x'])['auto_post'] === false, 'bad amount blocks auto-post');

check($R::normalizeAccountReference(' id: 12-345 678 ') === '12345678', 'account reference normalised');

// ================================================================ AI helper functions
$A = 'AiMatchingService';

check($A::maskAccount('12345678') === '*****678', 'mask keeps last 3 only');
check($A::maskAccount('12') === '**', 'short ref fully masked');
check($A::maskAccount('') === '', 'empty ref stays empty');

check($A::editDistance('12345678', '12345687') === 2, 'transposed digits = distance 2');
check($A::editDistance(str_repeat('a', 300), 'a') === PHP_INT_MAX, 'very long input does not fatal');

$rows = [
    ['id' => 1, 'id_number' => '12345687', 'member_number' => 'SH-001'],
    ['id' => 2, 'id_number' => '99999999', 'member_number' => 'SH-002'],
    ['id' => 3, 'id_number' => '12345678', 'member_number' => 'SH-003'],
];
$near = $A::nearAccountMatches('12345678', $rows, 2);
check(count($near) === 2 && $near[0]['member_id'] === 3 && $near[0]['distance'] === 0, 'near matches sorted, exact first');
check($near[1]['member_id'] === 1, 'typo match found');
check($A::nearAccountMatches('123', $rows, 2) === [], 'too-short reference is not fuzzy matched');

check($A::nameTokenOverlap('AMINA W OTIENO', 'Amina', 'Otieno') === 2, 'name token overlap counts shared tokens');
check($A::nameTokenOverlap('JOHN KAMAU', 'Amina', 'Otieno') === 0, 'no overlap');
check($A::phoneTail('+254 711-111-111') === '711111111', 'phone tail');

$valid = ['c1', 'c2'];
$p = $A::parseModelResponse('{"best_candidate":"c2","confidence":90,"rationale":"ok","ranking":["c2","c1"]}', $valid);
check($p['best_candidate'] === 'c2' && $p['ranking'] === ['c2', 'c1'], 'valid model JSON parsed');
$p = $A::parseModelResponse("```json\n{\"best_candidate\":\"c1\",\"confidence\":50,\"rationale\":\"r\",\"ranking\":[]}\n```", $valid);
check($p['best_candidate'] === 'c1', 'code-fenced JSON tolerated');
$p = $A::parseModelResponse('{"best_candidate":"c99","confidence":99,"rationale":"x"}', $valid);
check($p['best_candidate'] === null && $p['confidence'] === 0.0, 'unknown candidate key rejected (cannot invent a member)');
$p = $A::parseModelResponse('Sure! Post it to member 5.', $valid);
check($p['best_candidate'] === null, 'non-JSON output fails closed');
$p = $A::parseModelResponse('{"best_candidate":"c1","confidence":"high","rationale":"<script>x</script>y"}', $valid);
check($p['confidence'] === 0.0 && strpos($p['rationale'], '<script>') === false, 'bad confidence + html stripped');

$features = [
    'c1' => ['account_distance' => 1, 'phone_match' => false, 'name_token_overlap' => 0],
    'c2' => ['account_distance' => 7, 'phone_match' => false, 'name_token_overlap' => 0],
];
$g = $A::applyGuardrails(['best_candidate' => 'c1', 'confidence' => 100.0, 'rationale' => '', 'ranking' => []], $features);
check($g['confidence'] === 85.0, 'confidence capped at 85 even with evidence');
$g = $A::applyGuardrails(['best_candidate' => 'c2', 'confidence' => 95.0, 'rationale' => '', 'ranking' => []], $features);
check($g['confidence'] === 30.0, 'no hard evidence caps confidence at 30 (prompt-injection defence)');

// ================================================================ reconciliation flow (stubbed DB)
// 1. ID match + exact amount -> posted inside a transaction, SMS afterwards
$db = freshWorld();
Member::$byId = $member;
$res = (new PaymentReconciliationService())->autoReconcilePayment($paymentData());
check($res['matched'] === true && $res['payment_type'] === 'monthly', 'ID match with exact amount posts');
check(Member::$calls === ['monthly:7'], 'coverage extended exactly once');
check($db->pdo->log === ['BEGIN', 'COMMIT'], 'posting wrapped in one transaction');
check(count($GLOBALS['sms']) === 1, 'confirmation SMS sent after commit');
check($db->ran('payment_reconciliation_log'), 'audit row written');
check($db->ran('UPDATE mpesa_c2b_callbacks'), 'callback marked processed');

// 2. ID match but wrong amount -> review, nothing posted
$db = freshWorld();
Member::$byId = $member;
$res = (new PaymentReconciliationService())->autoReconcilePayment($paymentData(['amount' => 10]));
check($res['matched'] === false && !empty($res['review_required']), 'wrong amount routed to review');
check(Member::$calls === [], 'wrong amount does not extend coverage');
$params = $db->paramsFor('INSERT INTO payments');
check($params !== null && $params['reconciliation_status'] === 'unmatched', 'stored as unmatched');
check(strpos((string)$params['reconciliation_notes'], 'differs') !== false, 'reason saved for admin');

// 3. phone-only candidate -> never auto-posts
$db = freshWorld();
Member::$byPhone = $member;
$res = (new PaymentReconciliationService())->autoReconcilePayment($paymentData(['bill_ref_number' => '']));
check($res['matched'] === false && Member::$calls === [], 'phone-only match is not posted');
check(strpos((string)$db->paramsFor('INSERT INTO payments')['reconciliation_notes'], 'phone') !== false, 'phone-only reason recorded');

// 4. nothing matches
$db = freshWorld();
$res = (new PaymentReconciliationService())->autoReconcilePayment($paymentData());
check($res['matched'] === false && empty($res['review_required']), 'no candidate -> plain unmatched');

// 5. failure inside posting rolls everything back and surfaces the error
$db = freshWorld();
Member::$byId = $member;
Member::$throwOnApply = true;
$threw = false;
try {
    (new PaymentReconciliationService())->autoReconcilePayment($paymentData());
} catch (Exception $e) {
    $threw = true;
}
check($threw, 'posting error propagates so the callback can be retried');
check($db->pdo->log === ['BEGIN', 'ROLLBACK'], 'transaction rolled back on failure');
check($GLOBALS['sms'] === [], 'no SMS when posting failed');

// 6. receipt already recorded -> duplicate, no second posting
$db = freshWorld();
Member::$byId = $member;
Payment::$existing = ['id' => 9, 'member_id' => 7];
$res = (new PaymentReconciliationService())->autoReconcilePayment($paymentData());
check(!empty($res['duplicate']) && Member::$calls === [], 'existing receipt is a duplicate, not re-posted');

// 7. callback stored earlier but never became a payment -> retried, not swallowed
$db = freshWorld();
Member::$byId = $member;
$db->fetchMap = ['FROM mpesa_c2b_callbacks WHERE trans_id' => ['id' => 55, 'processed' => 0, 'payment_id' => null]];
$res = (new PaymentReconciliationService())->processC2BCallback([
    'TransID' => 'RCP123', 'TransTime' => '20261118100000', 'TransAmount' => 350,
    'BillRefNumber' => '12345678', 'MSISDN' => '0711111111', 'FirstName' => 'AMINA', 'LastName' => 'OTIENO',
]);
check(empty($res['duplicate']) && !empty($res['matched']), 'unprocessed stored callback is retried and posted');

// 8. processed callback with linked payment -> real duplicate
$db = freshWorld();
Payment::$existing = ['id' => 9, 'member_id' => 7];
$db->fetchMap = ['FROM mpesa_c2b_callbacks WHERE trans_id' => ['id' => 55, 'processed' => 0, 'payment_id' => null]];
$res = (new PaymentReconciliationService())->processC2BCallback(['TransID' => 'RCP123', 'TransAmount' => 350]);
check(!empty($res['duplicate']), 'callback with an existing payment is a duplicate');
check($db->ran('SET'), 'stale callback row gets linked/marked processed');

// 9. manual reconciliation cannot re-assign an already posted payment
$db = freshWorld();
Member::$rows[7] = $member;
Payment::$rows[40] = ['id' => 40, 'member_id' => 3, 'reconciliation_status' => 'matched', 'status' => 'completed', 'payment_type' => 'monthly'];
$svc = new PaymentReconciliationService();
check($svc->manualReconciliation(40, 7, 1, 'n') === false, 'cannot re-assign an already matched payment');
check($svc->getLastError() !== '', 'reason exposed to controller');
check(Member::$calls === [], 'no second coverage extension');

// 10. manual reconciliation of an unmatched payment works and syncs callback
$db = freshWorld();
Member::$rows[7] = $member;
Payment::$rows[41] = ['id' => 41, 'member_id' => null, 'reconciliation_status' => 'unmatched', 'status' => 'pending',
    'payment_type' => 'monthly', 'mpesa_receipt_number' => 'RCP555'];
$svc = new PaymentReconciliationService();
check($svc->manualReconciliation(41, 7, 1, 'checked ID card', 'ai_assisted') === true, 'unmatched payment reconciles');
check($db->paramsFor('payment_reconciliation_log')['action'] === 'ai_assisted_match', 'AI-assisted match is labelled in the audit log');
check($db->ran('UPDATE mpesa_c2b_callbacks'), 'manual match marks raw callback processed');
check($db->pdo->log === ['BEGIN', 'COMMIT'], 'manual match is transactional');

// ---------------------------------------------------------------- result
@unlink($tmpRoot . '/app/services/SmsService.php');
@rmdir($tmpRoot . '/app/services');
@rmdir($tmpRoot . '/app');
@rmdir($tmpRoot);

echo "{$GLOBALS['__passes']} passed, {$GLOBALS['__failures']} failed\n";
exit($GLOBALS['__failures'] > 0 ? 1 : 0);
