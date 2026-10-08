<?php
if (PHP_SAPI !== 'cli') exit;
require_once __DIR__.'/../app/services/RegistrationPlanService.php';
require_once __DIR__.'/../app/services/PlatinumBillingService.php';
require_once __DIR__.'/../app/services/RegistrationNotificationService.php';
require_once __DIR__.'/../app/services/SmsService.php';
require_once __DIR__.'/../app/services/SmsReviewService.php';
$membership_packages=require __DIR__.'/../config/packages.php';
$platinum_config=require __DIR__.'/../config/platinum.php';
class Database {
    public static $instance;
    public array $groups=[], $coverages=[], $events=[], $queue=[], $drafts=[];
    public bool $transaction=false, $failEvent=false;
    private array $snapshot=[];
    private int $last=0;
    public static function getInstance(){ return self::$instance; }
    public function getConnection(){ return $this; }
    public function inTransaction(){ return $this->transaction; }
    public function beginTransaction(){ $this->transaction=true; $this->snapshot=[$this->events,$this->queue,$this->drafts]; }
    public function commit(){ $this->transaction=false; }
    public function rollBack(){ [$this->events,$this->queue,$this->drafts]=$this->snapshot; $this->transaction=false; }
    public function lastInsertId(){ return $this->last; }
    public function query($sql,$p=[]){
        if (str_starts_with($sql,'SAVEPOINT')) { $this->snapshot=[$this->events,$this->queue,$this->drafts]; return; }
        if (str_starts_with($sql,'ROLLBACK TO')) { [$this->events,$this->queue,$this->drafts]=$this->snapshot; return; }
        if (str_starts_with($sql,'RELEASE')) return;
        if (str_contains($sql,'INSERT INTO sms_review_drafts')) { $this->last=count($this->drafts)+1; $this->drafts[$this->last]=$p; return; }
        throw new LogicException($sql);
    }
    public function fetch($sql,$p){
        if(str_contains($sql,'FROM registration_sms_events')) return $this->events[$p['id']]??null;
        if(str_contains($sql,'FROM members m')) return ['id'=>$p['id'],'package'=>'individual_below_70','package_key'=>'individual_below_70','monthly_contribution'=>100,'first_name'=>'Yambo','phone'=>'0712345678','member_number'=>'SH-123456','id_number'=>'12345678'];
        throw new LogicException($sql);
    }
    public function fetchAll($sql,$p){
        if(str_contains($sql,'member_corporate_members')) return $this->groups;
        if(str_contains($sql,'platinum_coverages')){
            if(!str_contains($sql,'registration_selected = 1')) throw new LogicException('Billing must recognize new pending selections');
            return array_values(array_filter($this->coverages,fn($c)=>$c['status']==='active'||($c['status']==='pending_approval'&&($c['registration_selected']??0)===1)));
        }
        throw new LogicException($sql);
    }
    public function insert($table,$row){
        if($table==='registration_sms_events'){
            if($this->failEvent) throw new RuntimeException('Simulated DB failure');
            if(isset($this->events[$row['member_id']])) throw new RuntimeException('Duplicate event');
            $this->events[$row['member_id']]=$row;return $row['member_id'];
        }
        $property=['platinum_coverages'=>'coverages','sms_queue'=>'queue'][$table]??null;
        if(!$property) throw new LogicException($table);
        $id=count($this->$property)+1;$this->{$property}[$id]=['id'=>$id]+$row;return $id;
    }
}
class BulkSmsService { public static int $attempts=0; public function processQueueByIds($ids){self::$attempts++;} }
class CaptureSms extends SmsService { public int $sent=0; public function sendApprovedSms($to,$message){$this->sent++;return ['success'=>true];} }
$checks=0;
function check($ok,$message){global $checks;if(!$ok)throw new RuntimeException($message);$checks++;}
function rejects($fn,$message){try{$fn();}catch(RuntimeException|InvalidArgumentException $e){check(true,$message);return;}throw new RuntimeException($message);}
$dob=(new DateTimeImmutable('today'))->modify('-35 years')->format('Y-m-d');
check(RegistrationPlanService::validate('0','individual_below_70',$dob)['amount']===100.0,'Basic exact price');
check(RegistrationPlanService::validate('1','individual_below_70',$dob)['amount']===300.0,'Platinum replaces Basic');
foreach ([['','individual_below_70',$dob],['1','missing',$dob],['1','individual_below_70','2020-02-31'],['1','individual_71_80',$dob]] as $args) rejects(fn()=>RegistrationPlanService::validate(...$args),'Invalid plan cannot silently register Basic');
$price=$platinum_config['prices']['individual']['under_70'];unset($platinum_config['prices']['individual']['under_70']);
rejects(fn()=>RegistrationPlanService::validate('1','individual_below_70',$dob),'Missing Platinum quote rejected');
$platinum_config['prices']['individual']['under_70']=$price;
$db=Database::$instance=new Database();
$db->groups=[['id'=>7,'label'=>'Additional','package_key'=>'individual_below_70','date_of_birth'=>$dob,'monthly_contribution'=>100]];
(new RegistrationPlanService())->apply(1,'1','individual_below_70',$dob);
check(count($db->coverages)===2,'Principal and additional group assigned selected tier');
foreach($db->coverages as $c) check($c['registration_selected']===1&&$c['status']==='pending_approval'&&$c['monthly_contribution']===300.0,'Selected price retained; benefit approval remains pending');
$member=$db->fetch('FROM members m',['id'=>1]);
$summary=(new PlatinumBillingService())->accountSummary($member);
check($summary['total']===600.0&&$summary['basic_due']===0.0,'No Basic plus Platinum double bill');
$db->groups[0]['date_of_birth']='';
rejects(fn()=>(new RegistrationPlanService())->apply(2,'1','individual_below_70',$dob),'Additional Platinum DOB mandatory');
check(count($db->coverages)===2,'Validation completes before any coverage writes');
$db->coverages=[];(new RegistrationPlanService())->apply(2,'0','individual_below_70',$dob);
check(!$db->coverages,'Basic has no Platinum enrollment');
$db->groups=[];
$db->coverages=[['status'=>'pending_approval','registration_selected'=>0]];
check((new PlatinumBillingService())->accountSummary($member)['total']===100.0,'Existing pending conversion is not billed');
$db->coverages=[];
foreach([['manager','/admin/members/register','POST',true],['agent','/agent/register-member/store','POST',true],['member','/admin/members/register','POST',false],['manager','/register/process','POST',false],['manager','/admin/members/register','GET',false],['agent','/callback','POST',false],['member','/inpatient-requests','POST',false]] as [$role,$path,$method,$expected]){
 $_SESSION=['user_id'=>9,'user_role'=>$role];$_SERVER['REQUEST_URI']=$path;$_SERVER['REQUEST_METHOD']=$method;
 check(SmsTriggerContext::isStaffAction()===$expected,"Draft provenance $role $path $method");
}
$auto=new CaptureSms();$auto->sendSms('0712345678','Application received',['source'=>'Admin','role'=>'manager']);
check($auto->sent===1&&!$db->drafts,'Member notification uses automatic transport despite forged context');
rejects(fn()=>(new SmsReviewService())->create('0712345678','Forged'), 'Member cannot create review drafts directly');
$_SESSION=['user_id'=>9,'user_role'=>'manager'];$_SERVER['REQUEST_URI']='/admin/members/register';
$notify=new RegistrationNotificationService();$notify->send(1,'https://example.test/set-password');
check(count($db->drafts)===1&&!$db->queue,'Genuine staff invite creates one draft');
$_SESSION=['user_id'=>1,'user_role'=>'member'];$_SERVER['REQUEST_URI']='/payments';
$notify->send(1);$notify->send(1);
check(count($db->drafts)===1&&!$db->queue,'Payment activation cannot duplicate staff welcome');
$notify->send(2);$notify->send(2);
check(count($db->queue)===1&&BulkSmsService::$attempts===1,'Automatic welcome enqueued and dispatched once');
$db->failEvent=true;
rejects(fn()=>$notify->send(3),'Event failure rolls back queue');
check(count($db->queue)===1&&!isset($db->events[3]),'Atomic event and queue rollback');
$db->failEvent=false;$notify->send(3);
check(count($db->queue)===2,'Failed transaction safely retried');
$db->beginTransaction();$notify->send(4);
check($db->inTransaction()&&BulkSmsService::$attempts===2,'Nested transaction never sends before commit');
$db->rollBack();check(!isset($db->events[4])&&count($db->queue)===2,'Outer rollback removes welcome');
$message=RegistrationNotificationService::message($member,['total'=>600,'breakdown'=>['principal_tier'=>'Platinum','corporate_count'=>1,'corporate_tier_label'=>'Platinum']]);
check(str_contains($message,'KES 600.00')&&str_contains($message,'approval')&&!str_contains($message,'Basic'),'Welcome states actual account total and benefit restriction');
echo "$checks registration tier and notification checks passed; no provider or live DB used.\n";
