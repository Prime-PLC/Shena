<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
require_once __DIR__ . '/../app/services/SmsService.php';
class SeasonalCaptureSms extends SmsService
{
    public DateTimeImmutable $clock;
    public array $sent = [];
    public function __construct(string $date) { $this->clock = new DateTimeImmutable($date); }
    protected function smsNow(): DateTimeImmutable { return $this->clock; }
    public function sendApprovedSms($to, $message) { $this->sent[] = ['approved', $to, $message]; return ['success'=>true]; }
    public function sendSms($to, $message, array $context = []) { $this->sent[] = ['draft', $to, $message, $context]; return ['success'=>true]; }
}
function verify($condition, $label) { if (!$condition) throw new RuntimeException($label); }
foreach ([
    '2026-10-04T20:59:59Z'=>false,
    '2026-10-04T21:00:00Z'=>true,
    '2026-10-09T20:59:59Z'=>true,
    '2026-10-09T21:00:00Z'=>false,
    '2027-10-05T12:00:00+03:00'=>false,
] as $time=>$active) verify(CustomerServiceWeekSms::isActive(new DateTimeImmutable($time))===$active,'Nairobi campaign boundary: '.$time);
$data=['amount'=>'600.0','transaction_id'=>'TEST123456'];
$service = new SeasonalCaptureSms('2026-10-07T12:00:00+03:00');
$service->sendPaymentConfirmationSms('0711111111',$data);
$expected='Payment confirmed! KES 600.0 received. Transaction ID: TEST123456. Happy Customer Service Week! Thank you for trusting Shena Companion. We value you.';
verify($service->sent[0]===['approved','0711111111',$expected], 'Automatic receipt must keep amount, reference and delivery route');
verify(strlen($expected)<=160,'Typical receipt fits one GSM SMS');
$long=['amount'=>'123456789.99','transaction_id'=>str_repeat('X',30)];
verify(str_contains(CustomerServiceWeekSms::paymentConfirmation($long,$service->clock),$long['transaction_id']),'Never truncate transaction reference to fit SMS');
$service->sendWelcomeSms('0711111111',['member_number'=>'SHENA001']);
$service->sendActivationSms('0711111111',['member_number'=>'SHENA001']);
foreach ([1,2] as $i) {
    verify($service->sent[$i][0]==='draft','Keep review flow for membership messages');
    verify(str_contains($service->sent[$i][2],'SHENA001'),'Keep member number');
    verify(str_contains($service->sent[$i][2],CustomerServiceWeekSms::APPRECIATION),'Add appreciation');
}
$service->sendPaymentReminderSms('0711111111',['amount'=>'600','member_number'=>'SHENA001']);
$service->sendClaimStatusSms('0711111111',['status'=>'approved','approved_amount'=>'20000']);
foreach ([3,4] as $i) verify(!str_contains($service->sent[$i][2],'Customer Service Week'),'Exclude reminders and sensitive claim messages');
$plain='Hi Test! Welcome to SHENA. Pay KES 600 by the 7th via Paybill 4163987, Acct: 1234. Member SHENA001.';
verify(CustomerServiceWeekSms::appreciate($plain,$service->clock)===$plain.' '.CustomerServiceWeekSms::APPRECIATION,'Welcome details retained');
$service->clock=new DateTimeImmutable('2026-10-10T00:00:00+03:00');
$service->sendPaymentConfirmationSms('0711111111',$data);
verify(end($service->sent)[2]==='Payment confirmed! KES 600.0 received. Transaction ID: TEST123456. Thank you. - Shena Companion','Restore original receipt after week');
verify(CustomerServiceWeekSms::appreciate($plain,$service->clock)===$plain,'Restore original welcome after week');
echo "Customer Service Week SMS tests passed\n";
