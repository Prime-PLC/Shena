<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
require_once __DIR__.'/../app/services/ClaimReceiptService.php';
class Database {
    private static $instance;
    public bool $transaction=false;
    public static function getInstance() { return self::$instance ??= new self; }
    public function fetch($sql,$p) { return ['phone'=>'0711111111','first_name'=>'Test']; }
    public function getConnection() { return $this; }
    public function beginTransaction() { $this->transaction=true; }
    public function commit() { $this->transaction=false; }
    public function rollBack() { $this->transaction=false; }
    public function inTransaction() { return $this->transaction; }
}
class SmsService {
    public function formatPhoneNumber($p) { return preg_replace('/^0/','254',$p); }
    public function validatePhoneNumber($p) { return (bool)preg_match('/^254[17][0-9]{8}$/',$p); }
}
class ClaimEvidenceService {
    public function find($type,$id) { return ['member_id'=>1]; }
    public static function deadline($type,$case) { return new DateTimeImmutable('2026-10-08 12:00:00'); }
}
class BulkSmsService {
    public static array $queued=[], $processed=[];
    public function queueQuickSms($recipients,$message) { $ids=[]; foreach ($recipients as $r) { self::$queued[]=$r+['message'=>$message];$ids[]=count(self::$queued); } return $ids; }
    public function processQueueByIds($ids) { if (Database::getInstance()->transaction) throw new Exception('Transport before commit'); self::$processed=$ids; }
}
putenv('CLAIM_ADMIN_PHONES=0748585067,0748585071');
(new ClaimReceiptService())->notify('funeral',23);
if (count(BulkSmsService::$queued)!==3 || BulkSmsService::$processed!==[1,2,3]) throw new Exception('Must queue member and both admins');
if (BulkSmsService::$queued[1]['phone']===BulkSmsService::$queued[2]['phone']) throw new Exception('Distinct admins required');
if (!str_contains(BulkSmsService::$queued[0]['message'],'7 days from filing')) throw new Exception('Receipt missing next steps');
putenv('CLAIM_ADMIN_PHONES=0748585067,254748585067');
try { ClaimReceiptService::adminPhones(); throw new Exception('Duplicate phones accepted'); } catch (RuntimeException $e) {}
putenv('CLAIM_ADMIN_PHONES');
echo "Claim receipt tests passed\n";
