<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
require_once __DIR__ . '/../app/services/ClaimEvidenceService.php';
class Database {
    public array $case = ['created_at'=>'2026-10-01 09:30:00', 'status'=>'submitted', 'admin_created'=>1];
    public array $docs = [], $review = [];
    private static $instance;
    public static function getInstance() { return self::$instance ??= new self; }
    public function fetch($sql,$params) { return str_contains($sql,'claim_evidence_reviews') ? $this->review : $this->case; }
    public function fetchAll($sql,$params) { return $this->docs; }
}
class ClaimDocument { public function getClaimDocuments($id) { return Database::getInstance()->docs; } }
function check($condition,$message) { if (!$condition) throw new RuntimeException($message); }
function blocked($service,$type,$message) { try { $service->assertReady($type,1); } catch (RuntimeException $e) { check(str_contains($e->getMessage(),$message),$e->getMessage()); return; } throw new RuntimeException('Approval unexpectedly allowed'); }
$db=Database::getInstance();$service=new ClaimEvidenceService();
check(ClaimEvidenceService::deadline('funeral',$db->case)->format('Y-m-d H:i:s')==='2026-10-08 09:30:00','Seven days from filing');
blocked($service,'funeral','Upload required');
foreach (array_keys(ClaimEvidenceService::required('funeral')) as $type) $db->docs[]=['document_type'=>$type,'created_at'=>'2026-10-08 09:30:00'];
blocked($service,'funeral','Accept and verify');
$db->review=['accepted_at'=>'2026-10-07 15:00:00'];$service->assertReady('funeral',1);
$db->docs[0]['created_at']='2026-10-08 09:30:01';blocked($service,'funeral','after the deadline');
$db->review['exception_reason']='Member contacted office; late certified copy approved.';$service->assertReady('funeral',1);
$db->docs=[];blocked($service,'funeral','Upload required');
$db->case=['admission_date'=>'2026-10-01','status'=>'submitted'];$db->review=[];
check(ClaimEvidenceService::deadline('inpatient',$db->case)->format('Y-m-d H:i:s')==='2026-10-01 23:59:59','Admission day deadline');
$db->docs=[['document_type'=>'admission_proof','created_at'=>'2026-10-01 23:59:59']];$service->assertReady('inpatient',1);
$db->docs[0]['created_at']='2026-10-02 00:00:00';blocked($service,'inpatient','after the deadline');
$db->review=['exception_reason'=>'Hospital admission confirmed by phone; late proof authorized.'];$service->assertReady('inpatient',1);
// Re-uploading later must not invalidate an earlier valid submission.
$db->review=[];$db->docs[]=['document_type'=>'admission_proof','created_at'=>'2026-10-01 10:00:00'];$service->assertReady('inpatient',1);
try { ClaimEvidenceService::required('arbitrary_table'); throw new Exception('Invalid type allowed'); } catch (InvalidArgumentException $e) {}
echo "Claim document policy tests passed\n";
