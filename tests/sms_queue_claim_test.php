<?php
require_once __DIR__ . '/../app/core/BaseModel.php';
require_once __DIR__ . '/../app/services/BulkSmsService.php';
class QueueClaimDb {
    public string $status = 'pending';
    public bool $loseClaim = false;
    public function prepare($sql) { return new QueueClaimStatement($this, $sql); }
}
class QueueClaimStatement {
    private int $affected = 0;
    public function __construct(private QueueClaimDb $db, private string $sql) {}
    public function execute($params) {
        if (str_contains($this->sql, "SET status = 'processing'")) {
            $this->affected = !$this->db->loseClaim && $this->db->status === 'pending' ? 1 : 0;
            if ($this->affected) $this->db->status = 'processing';
        } elseif (str_contains($this->sql, 'UPDATE sms_queue')) { $this->db->status = $params[0]; }
        return true;
    }
    public function fetchAll($mode) { return [['id' => 1, 'phone_number' => '254712345678', 'message' => 'Synthetic test']]; }
    public function rowCount() { return $this->affected; }
}
class QueueClaimTransport {
    public int $calls = 0;
    public bool $throw = false;
    public bool $unknown = false;
    public function sendApprovedSms($phone, $message) {
        $this->calls++;
        if ($this->throw) throw new RuntimeException('Uncertain network outcome');
        if ($this->unknown) return ['success' => false, 'status' => 'unknown', 'error' => 'Provider timeout'];
        return ['success' => true, 'provider_message_id' => 'fixture'];
    }
}
$checks = 0;
foreach (['processQueue', 'processQueueByIds'] as $method) {
    $service = (new ReflectionClass(BulkSmsService::class))->newInstanceWithoutConstructor();
    $db = new QueueClaimDb(); $sms = new QueueClaimTransport();
    (new ReflectionProperty(BulkSmsService::class, 'db'))->setValue($service, $db);
    (new ReflectionProperty(BulkSmsService::class, 'smsService'))->setValue($service, $sms);
    $arg = $method === 'processQueue' ? 1 : [1];
    $db->loseClaim = true; $service->$method($arg);
    if ($sms->calls !== 0) throw new RuntimeException('Losing worker must not send'); $checks++;
    $db->loseClaim = false; $service->$method($arg);
    if ($sms->calls !== 1 || $db->status !== 'submitted') throw new RuntimeException('Winning worker submits once'); $checks++;
    $service->$method($arg); // Stale SELECT result deliberately returned by fixture.
    if ($sms->calls !== 1) throw new RuntimeException('Stale worker must not submit again'); $checks++;
    $db->status = 'pending'; $sms->unknown = true; $service->$method($arg);
    if ($db->status !== 'unknown') throw new RuntimeException('Returned uncertainty must not permit retry'); $checks++;
    $sms->unknown = false;
    $db->status = 'pending'; $sms->throw = true; $service->$method($arg);
    if ($db->status !== 'unknown') throw new RuntimeException('Uncertain submission must not be marked safe to retry'); $checks++;
}
echo "$checks worker claim checks passed. No provider called.\n";
