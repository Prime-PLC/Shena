<?php
/** Transactional receipts bypass the business-message draft review, using the delivery queue. */
class ClaimReceiptService
{
    public static function adminPhones(): array
    {
        $phones = explode(',', getenv('CLAIM_ADMIN_PHONES') ?: '0748585067,0748585071');
        $sms = new SmsService();
        $phones = array_values(array_unique(array_map([$sms, 'formatPhoneNumber'], array_map('trim', $phones))));
        if (count($phones) !== 2) throw new RuntimeException('Configure exactly two distinct CLAIM_ADMIN_PHONES.');
        foreach ($phones as $phone) if (!$sms->validatePhoneNumber($phone)) throw new RuntimeException('Invalid claim admin phone.');
        return $phones;
    }
    public function notify(string $type, int $id): void
    {
        try {
            $db = Database::getInstance();
            $case = (new ClaimEvidenceService())->find($type, $id);
            $member = $db->fetch('SELECT u.phone, u.first_name FROM members m JOIN users u ON u.id = m.user_id WHERE m.id = :id', ['id'=>$case['member_id']]);
            $due = ClaimEvidenceService::deadline($type, $case)->format('d M Y H:i');
            $instruction = $type === 'funeral' ? "Upload ID/birth certificate, chief letter and mortuary invoice by {$due} EAT (7 days from filing)." : "Upload admission proof on the admission day. If late, call 0748585067 or 0748585071 for guidance.";
            $queue = new BulkSmsService();
            $ids = [];
            $connection = $db->getConnection();
            $connection->beginTransaction();
            try {
                $ids = $queue->queueQuickSms([['phone'=>$member['phone'] ?? '']], 'Dear ' . ($member['first_name'] ?? 'Member') . ", SHENA received {$type} claim #{$id}. Application incomplete pending verification and documents. {$instruction} Open your portal claim documents page.");
                $recipients = array_map(function ($phone) { return ['phone'=>$phone]; }, self::adminPhones());
                $ids = array_merge($ids, $queue->queueQuickSms($recipients, "SHENA: New {$type} claim #{$id} received. Review the application and required documents in the admin portal. Deadline: {$due} EAT."));
                $connection->commit();
            } catch (Throwable $e) { if ($connection->inTransaction()) $connection->rollBack(); throw $e; }
            // Immediate attempt; pending messages remain available to the existing queue worker.
            $queue->processQueueByIds($ids);
        } catch (Throwable $e) {
            error_log('Claim receipt notification failed for ' . $type . '#' . $id . ': ' . $e->getMessage());
            $_SESSION['evidence_message'] = 'Application saved. Receipt notification could not be completed; please contact the office.';
        }
    }
}
