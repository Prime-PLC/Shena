<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
require_once __DIR__ . '/../app/core/BaseModel.php';
require_once __DIR__ . '/../app/models/ServiceProvider.php';
class Database {
    public static $instance;
    public array $providers=[], $assignments=[], $drafts=[], $logs=[], $queue=[];
    public array $case=['id'=>1,'deceased_name'=>'Synthetic Person','patient_name'=>'Synthetic Patient','status'=>'approved'];
    public int $last=0;
    public static function getInstance() {return self::$instance;}
    public function getConnection() {return $this;}
    public function lastInsertId() {return $this->last;}
    public function fetch($sql,$p) {
        if (str_contains($sql,'FROM claims ') || str_contains($sql,'FROM inpatient_requests ')) return $p['id']===1 ? $this->case : null;
        if (str_contains($sql,'FROM claim_service_checklist')) return ['id'=>1];
        if (str_contains($sql,'FROM service_providers')) {
            if (isset($p['phone'])) {foreach ($this->providers as $r) if ($r['id']!==$p['id'] && ($r['phone']===$p['phone'] || strcasecmp($r['provider_identity'],$p['identity'])===0)) return $r; return null;}
            return $this->providers[$p['id']] ?? null;
        }
        if (str_contains($sql,'FROM claim_provider_assignments')) {
            if (isset($p['id'])) return $this->assignments[$p['id']] ?? null;
            foreach ($this->assignments as $r) {
                if (isset($p['draft_id']) && $r['draft_id']===$p['draft_id']) return $r;
                if (isset($p['type']) && $r['case_type']===$p['type'] && $r['case_id']===$p['case_id'] && $r['stage_key']===$p['stage']) return $r;
            } return null;
        }
        if (str_contains($sql,'FROM sms_review_drafts')) return $this->drafts[$p['id']] ?? null;
        if (str_contains($sql,'FROM sms_queue')) return null;
        throw new LogicException($sql);
    }
    public function fetchAll($sql,$p=[]) {
        if (str_contains($sql,'FROM claim_provider_assignments')) return array_values(array_filter($this->assignments,fn($r)=>$r['provider_id']===$p['id']));
        if (str_contains($sql,'FROM sms_review_drafts')) return array_values($this->drafts);
        throw new LogicException($sql);
    }
    public function insert($table,$row) {
        $field=['service_providers'=>'providers','claim_provider_assignments'=>'assignments','activity_logs'=>'logs','sms_queue'=>'queue'][$table];
        $id=count($this->$field)+1;$this->{$field}[$id]=['id'=>$id]+$row;return $id;
    }
    public function update($table,$row,$where,$p) {
        $field=['service_providers'=>'providers','claim_provider_assignments'=>'assignments','sms_review_drafts'=>'drafts'][$table];
        $this->{$field}[$p['id']]=array_replace($this->{$field}[$p['id']],$row);
    }
    public function query($sql,$p) {
        if (str_starts_with($sql,'UPDATE sms_review_drafts')) {if (($this->drafts[$p['id']]['status']??'')==='draft') {$this->drafts[$p['id']]['status']='discarded';$this->drafts[$p['id']]['open_key']=null;}return;}
        if (str_starts_with($sql,'INSERT INTO sms_review_drafts')) {
            $id=count($this->drafts)+1; $this->drafts[$id]=['id'=>$id,'phone_number'=>$p['phone'],'message'=>$p['message'],'source'=>$p['source'],'open_key'=>$p['open_key'],'created_by'=>$p['actor'],'status'=>'draft','version'=>1];$this->last=$id;return;
        }
        throw new LogicException($sql);
    }
}
$checks=0;
function verify($ok,$label) {global $checks;if (!$ok) throw new RuntimeException($label);$checks++;}
function rejects($fn,$label) {try {$fn();}catch(RuntimeException $e){verify(true,$label);return;}throw new RuntimeException($label);}
$_SESSION=['user_id'=>9,'user_role'=>'manager'];$db=Database::$instance=new Database();$model=new ServiceProvider();
foreach (ServiceProvider::STAGES as $stage=>$label) foreach (ServiceProvider::STAGES as $other=>$unused) {
    $type=$stage==='platinum_hospital'?'platinum':'funeral';
    ServiceProvider::validateStage($type,$stage,$other);verify(true,'Provider may serve any valid stage');
}
rejects(fn()=>ServiceProvider::validateDetails(['stage_key'=>'coffin','phone'=>'0712345678']),'Name required');
rejects(fn()=>ServiceProvider::validateDetails(['stage_key'=>'invalid','phone'=>'0712345678','contact_name'=>'Test']),'Known stage required');
$details=['stage_key'=>'coffin','contact_name'=>'Test Contact','business_name'=>'Synthetic Coffins','phone'=>'0712345678'];
$id=$model->saveProvider(0,$details,9);verify($db->providers[$id]['phone']==='254712345678','Phone normalized');
verify($model->saveProvider(0,$details,9)>$id,'Repeated name and phone allowed');
$model->saveProvider($id,array_replace($details,['stage_key'=>'equipment']),9);verify(true,'Primary service editable');
rejects(fn()=>$model->assign('funeral',999,'coffin',$id,9),'Missing claim rejected');
rejects(fn()=>$model->assign('funeral',1,'platinum_hospital',$id,9),'Hospital stage rejected on funeral claim');
$model->assign('funeral',1,'coffin',$id,9);verify(count($db->assignments)===1 && !$db->drafts && !$db->queue,'Assignment sends nothing');
$draft=$model->contact('funeral',1,'coffin',9);verify(!$db->queue && $db->drafts[$draft]['status']==='draft','Contact only drafts');
verify($model->contact('funeral',1,'coffin',9)===$draft,'Reopening retains draft edits');
$model->assertCurrentDraft($draft);verify(true,'Current draft valid');
$model->assign('funeral',1,'coffin',0,9);verify($db->drafts[$draft]['status']==='discarded' && !$db->assignments[1]['provider_id'],'Removing assignment invalidates draft');
rejects(fn()=>$model->assertCurrentDraft($draft),'Detached draft blocked');
$model->assign('funeral',1,'coffin',$id,9);$next=$model->contact('funeral',1,'coffin',9);
$model->saveProvider($id,array_replace($details,['phone'=>'0799999999']),9);verify($db->drafts[$next]['status']==='discarded','Contact edit invalidates pending SMS');
$next=$model->contact('funeral',1,'coffin',9);verify($db->drafts[$next]['phone_number']==='254799999999','New draft uses new contact');
$db->case['status']='rejected';rejects(fn()=>$model->assertCurrentDraft($next),'Closed request blocks delivery');$db->case['status']='approved';
$model->saveProvider($id,[],9,true);verify($db->providers[$id]['active']===0 && $db->drafts[$next]['status']==='discarded','Directory removal invalidates SMS');
rejects(fn()=>$model->contact('funeral',1,'coffin',9),'Removed provider cannot be contacted');
$hospital=$model->saveProvider(0,['stage_key'=>'platinum_hospital','business_name'=>'Synthetic Hospital','phone'=>'0711000000'],9);
$model->assign('platinum',1,'platinum_hospital',$hospital,9);$hospitalDraft=$model->contact('platinum',1,'platinum_hospital',9);
verify(str_contains($db->drafts[$hospitalDraft]['message'],'hospital care for Synthetic Patient'),'Platinum provider draft');
verify(!$db->queue && count($db->logs)>0,'Audited workflow never auto-sends');
$providerCaseType='funeral'; $providerCaseId=1; $providerStage='coffin';
$providerDirectory=array_values($db->providers);
$providerCaseAssignments=['funeral:1'=>['coffin'=>['provider_id'=>$id,'contact_name'=>'<script>bad</script>','business_name'=>'Synthetic Coffins','phone'=>'254712345678','email'=>'','provider_active'=>0]]];
ob_start(); include __DIR__.'/../resources/views/partials/claim-provider.php'; $html=ob_get_clean();
verify(str_contains($html,'Synthetic Hospital'),'Provider available across service categories');
verify(!str_contains($html,'<script>bad</script>') && str_contains($html,'&lt;script&gt;'),'Provider contact text escaped');
verify(str_contains($html,'disabled') && str_contains($html,'value="remove"'),'Removed provider cannot notify but can be detached');
$review = new SmsReviewService();
$queueId=$review->review($hospitalDraft,9,true,SmsReviewService::token($db->drafts[$hospitalDraft]),'Hello clinic, please call SHENA to arrange this request.','send');
verify($queueId===1 && $db->queue[1]['message']==='Hello clinic, please call SHENA to arrange this request.','Explicit review queues exact edited provider SMS');
rejects(fn()=>$review->review($hospitalDraft,9,true,SmsReviewService::token($db->drafts[$hospitalDraft]),'Again','send'),'Provider double-send rejected');
verify(count($db->queue)===1,'Only one approved queue entry');
$model->assign('funeral',1,'equipment',$hospital,9);
verify(count(array_filter($db->assignments, fn($a)=>$a['provider_id']===$hospital))===2,'Same provider serves hospital and funeral categories');
echo "$checks provider workflow checks passed. No database or SMS provider used.\n";
