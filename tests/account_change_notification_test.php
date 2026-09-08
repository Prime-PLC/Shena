<?php
/** Offline behavior tests: real summary, billing and notification services; no provider or database. */
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/services/MembershipPricingService.php';
require_once __DIR__ . '/../app/services/PlatinumBillingService.php';
require_once __DIR__ . '/../app/services/AccountChangeNotificationService.php';
$membership_packages = require __DIR__ . '/../config/packages.php';
$platinum_config = require __DIR__ . '/../config/platinum.php';
class AccountSmsDbFixture {
    public array $groups = [];
    public array $coverages = [];
    public array $queue = [];
    public bool $cooldown = false;
    public function fetchAll($sql, $params) {
        if (str_contains($sql, 'member_corporate_members')) return $this->groups;
        if (str_contains($sql, 'platinum_coverages')) return $this->coverages;
        throw new LogicException('Unexpected query');
    }
    public function fetch($sql, $params) {
        if (str_contains($sql, 'DATE_SUB')) return $this->cooldown ? ['id' => 99] : null;
        if (str_contains($sql, 'FROM sms_queue')) return $this->queue ? end($this->queue) : null;
        throw new LogicException('Unexpected query');
    }
    public function insert($table, $fields) {
        if ($table !== 'sms_queue') throw new LogicException('Unexpected table');
        $fields['id'] = count($this->queue) + 1; $this->queue[] = $fields; return $fields['id'];
    }
}
$db = new AccountSmsDbFixture();
(new ReflectionProperty(Database::class, 'instance'))->setValue(null, $db);
$service = new AccountChangeNotificationService();
$member = ['id' => 101, 'user_id' => 201, 'first_name' => 'Test Second', 'phone' => '0712345678', 'package_key' => 'couple_children_parents_70_80', 'monthly_contribution' => 9999];
$db->groups = [['id' => 301, 'label' => 'Additional person', 'package_key' => 'individual_below_70', 'monthly_contribution' => 100]];
$db->coverages = [['id' => 1, 'covered_person_type' => 'principal', 'covered_person_id' => null, 'monthly_contribution' => 550, 'maturity_date' => date('Y-m-d', strtotime('+4 months'))]];
$count = 0;
function check($condition, $message) { global $count; $count++; if (!$condition) throw new RuntimeException($message); }
function blocked($callback, $message) { global $count; $count++; try { $callback(); } catch (RuntimeException $e) { return; } throw new RuntimeException($message); }
$preview = $service->preview($member);
check(str_contains($preview['message'], 'Your total monthly payment is KES 650.00'), 'Must use live group sum, not stored 9999 baseline');
check(str_starts_with($preview['message'], 'Hi Test,') && !str_contains($preview['message'], 'Second'), 'Use only first name');
check(str_contains($preview['message'], 'Additional is on a separate Basic plan.'), 'Identify additional cover without implying whole account converted');
check(str_contains($preview['message'], 'waiting period for Platinum hospital benefits ends on'), 'Retain waiting period');
check(!str_contains($preview['message'], 'including all these plans'), 'Omit unnecessary total qualifier');
check($preview['phone'] === '254712345678', 'Normalize Kenyan number');
check($db->queue === [], 'Preview must never queue or send');
blocked(fn() => $service->queue($member, 'stale'), 'Reject stale preview');
check($db->queue === [], 'Stale preview must not write');
check($service->queue($member, $preview['token']) === 1, 'Queue reviewed summary');
check($db->queue[0]['status'] === 'pending', 'Do not claim delivered on enqueue');
blocked(fn() => $service->queue($member, $preview['token']), 'Block repeated submission');
check(count($db->queue) === 1, 'Exactly one queue record');
$db->coverages[0]['monthly_contribution'] = 650;
blocked(fn() => $service->queue($member, $preview['token']), 'Reject price changed after preview');
$fresh = $service->preview($member);
$db->cooldown = true;
blocked(fn() => $service->queue($member, $fresh['token']), 'Block rapid correction messages');
$db->cooldown = false;
check($service->queue($member, $fresh['token']) === 2, 'Allow reviewed changed summary after cooldown');
$db->coverages[0]['monthly_contribution'] = 550;
check($service->queue($member, $service->preview($member)['token']) === 3, 'Allow a later legitimate return to an earlier account state');
$badPhone = array_replace($member, ['phone' => 'invalid']);
blocked(fn() => $service->preview($badPhone), 'Reject invalid phone');
$changedPhone = array_replace($member, ['phone' => '0799999999']);
blocked(fn() => $service->queue($changedPhone, $preview['token']), 'Reject changed recipient after preview');
$badPackage = array_replace($member, ['package_key' => '', 'package' => 'family']);
blocked(fn() => $service->preview($badPackage), 'Reject ambiguous Basic package');
$edited = str_repeat('Please contact SHENA for help. ', 8);
check($service->queue($member, $service->preview($member)['token'], $edited) === 4, 'Allow admin edits longer than one segment');
check(end($db->queue)['message'] === trim($edited), 'Queue exact reviewed long message');
blocked(fn() => $service->queue($member, $service->preview($member)['token'], str_repeat('x',1001)), 'Retain shared 1000-character limit');
$beforeStateChange = $service->preview($member);
$db->coverages[0]['maturity_date'] = '2030-01-01';
blocked(fn() => $service->queue($member, $beforeStateChange['token']), 'Short text still detects changed waiting period');
$conversion = $service->preview($member);
$_SESSION['account_sms_pending'][101] = ['type'=>'amount_update','old_amount'=>400,'new_amount'=>650];
$amountPreview = $service->preview($member);
check(str_contains($amountPreview['message'], 'monthly payment has changed from KES 400.00 to KES 650.00'), 'Amount edit describes old and current live amount');
check(!str_contains($amountPreview['message'], 'is now Platinum') && !str_contains($amountPreview['message'], 'waiting period'), 'Amount edit does not reuse conversion wording');
blocked(fn() => $service->queue($member, $conversion['token'], $conversion['message']), 'Previous conversion tab cannot submit after an amount edit');
$_SESSION['account_sms_pending'][101] = ['type'=>'package_update','old_package'=>'individual_below_70','new_package'=>'couple_children_parents_71_80','old_amount'=>650];
$packagePreview = $service->preview($member);
check(str_contains($packagePreview['message'], 'changed your SHENA package from Individual') && str_contains($packagePreview['message'], 'to Couple, Children'), 'Same-price package change explains both packages');
blocked(fn() => $service->queue($member, $amountPreview['token'], $amountPreview['message']), 'Previous amount preview cannot submit after a package edit');
unset($_SESSION['account_sms_pending'][101]);
$db->coverages[] = $db->coverages[0];
blocked(fn() => $service->preview($member), 'Reject duplicate group coverage');
echo "$count notification checks passed. No real SMS sent.\n";
