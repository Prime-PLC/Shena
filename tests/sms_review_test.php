<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
require_once __DIR__ . '/../app/services/SmsReviewService.php';
require_once __DIR__ . '/../app/services/SmsService.php';
class Database {
    public static $instance;
    public array $drafts = [], $queue = [];
    private int $last = 0;
    public static function getInstance() { return self::$instance; }
    public function getConnection() { return $this; }
    public function lastInsertId() { return $this->last; }
    public function query($sql, $p) {
        foreach ($this->drafts as &$row) if ($row['open_key'] === $p['open_key']) {
            if ($row['message'] !== $p['message']) $row['version']++;
            $row['message'] = $p['message']; $this->last = $row['id']; return;
        }
        $id = count($this->drafts) + 1;
        $this->drafts[$id] = ['id'=>$id, 'phone_number'=>$p['phone'], 'message'=>$p['message'], 'source'=>$p['source'], 'open_key'=>$p['open_key'], 'created_by'=>$p['actor'], 'version'=>1, 'status'=>'draft'];
        $this->last = $id;
    }
    public function fetch($sql, $p) {
        if (str_contains($sql, 'FROM sms_review_drafts')) return $this->drafts[$p['id']] ?? null;
        if (str_contains($sql, 'FROM sms_queue')) {
            foreach ($this->queue as $row) if ($row['phone_number'] === $p['phone'] && $row['message'] === $p['message'] && $row['status'] !== 'failed') return $row;
            return null;
        }
        throw new LogicException($sql);
    }
    public function fetchAll($sql, $p) { return array_values($this->drafts); }
    public function insert($table, $row) { if ($table !== 'sms_queue') throw new LogicException($table); $id=count($this->queue)+1; $this->queue[$id]=['id'=>$id]+$row; return $id; }
    public function update($table, $row, $where, $p) { $this->drafts[$p['id']] = array_replace($this->drafts[$p['id']], $row); }
}
$checks=0;
function check($condition, $label) { global $checks; if (!$condition) throw new RuntimeException($label); $checks++; }
function rejects($fn, $label) { try {$fn();} catch (RuntimeException $e) {check(true,$label); return;} throw new RuntimeException($label); }
$db=Database::$instance=new Database(); $service=new SmsReviewService();
$_SERVER['REQUEST_METHOD']='POST'; $_SERVER['REQUEST_URI']='/admin/members/register';
$_SESSION=['user_id'=>9,'user_role'=>'manager'];
$result=(new SmsService())->sendSms('0712345678','Your payment is received.');
check($result['status']==='draft' && !$result['submitted'] && !$db->queue,'Business trigger only drafts');
$id=$result['draft_id']; $token=SmsReviewService::token($db->drafts[$id]);
check($service->create('+254712345678','Your payment is received.')===$id && count($db->drafts)===1,'Duplicate trigger merges');
rejects(fn()=>$service->review($id,10,false,$token,'Changed','send'),'Agent ownership');
rejects(fn()=>$service->review($id,9,true,'stale','Changed','send'),'Stale token');
rejects(fn()=>$service->review($id,9,true,$token,'Changed','unknown'),'Unknown decision');
rejects(fn()=>$service->review($id,9,true,$token,'','send'),'Blank text');
rejects(fn()=>SmsReviewService::validateMessage(str_repeat('x',1001)),'Length cap');
rejects(fn()=>SmsReviewService::phone('123'),'Invalid recipient');
check(SmsReviewService::validateMessage("  Thank you\r\nSHENA  ")==="Thank you\nSHENA",'Newline normalization');
$service->review($id,9,true,$token,'Thank you for your payment.','save');
check(!$db->queue && $db->drafts[$id]['status']==='draft','Save is not send');
rejects(fn()=>$service->review($id,9,true,$token,'Old content','send'),'Old saved preview rejected');
$q=$service->review($id,9,true,SmsReviewService::token($db->drafts[$id]),'We received your payment. Thank you.','send');
check($q===1 && $db->queue[1]['message']==='We received your payment. Thank you.' && $db->drafts[$id]['open_key']===null,'Explicit send queues exact edits');
rejects(fn()=>$service->review($id,9,true,SmsReviewService::token($db->drafts[$id]),'Again','send'),'Double submit rejected');
$next=$service->create('0712345678','Another event');
rejects(fn()=>$service->review($next,9,true,SmsReviewService::token($db->drafts[$next]),'We received your payment. Thank you.','send'),'Recent duplicate rejected');
$service->review($next,9,true,SmsReviewService::token($db->drafts[$next]),'','discard');
check(count($db->queue)===1 && $db->drafts[$next]['status']==='discarded','Discard never sends');
$replacement=$service->create('0712345678','Old amount',['key'=>'account']); $old=SmsReviewService::token($db->drafts[$replacement]);
check($service->create('0712345678','New amount',['key'=>'account'])===$replacement,'Correction replaces open draft');
rejects(fn()=>$service->review($replacement,9,true,$old,'Old amount','send'),'Correction invalidates preview');
$packages=require __DIR__.'/../config/packages.php';
foreach (['individual','couple_children_parents','couple_children_parents_inlaws','executive'] as $prefix) foreach ([18,69,70,71,80,81,90,91,100] as $age) {
 $matches=array_filter($packages,fn($p,$key)=>empty($p['legacy_alias']) && preg_match('/^'.preg_quote($prefix,'/').'_(?:below|above|[0-9])/', $key) && $age >= $p['age_min'] && $age <= $p['age_max'],ARRAY_FILTER_USE_BOTH);
 check(count($matches)===1,"Exactly one $prefix band at $age");
}
echo "$checks SMS review and boundary checks passed. No database or provider used.\n";
