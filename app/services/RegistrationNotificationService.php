<?php
require_once __DIR__ . '/SmsTriggerContext.php';
require_once __DIR__ . '/CustomerServiceWeekSms.php';

/** One durable welcome event shared by invitation and registration-fee activation. */
class RegistrationNotificationService
{
    public static function message(array $member, array $summary, ?string $inviteLink = null): string
    {
        $plan = $summary['breakdown'];
        $tier = $plan['principal_tier'];
        $name = $member['first_name'] ?: 'Member';
        $message = "Hi {$name}! Welcome to SHENA {$tier}. Member No: {$member['member_number']}.";
        if ($inviteLink) $message .= " Set your password: {$inviteLink} (valid 48 hrs).";
        $message .= ' Total monthly contribution: KES ' . number_format($summary['total'], 2)
            . ' by the 7th via Paybill 4163987, Acct: ' . ($member['id_number'] ?: $member['member_number']) . '.';
        if ($plan['corporate_count'] > 0) $message .= ' Includes ' . $plan['corporate_count'] . ' additional group(s) on ' . $plan['corporate_tier_label'] . '.';
        if ($tier === 'Platinum') $message .= ' Platinum benefits require approval and completion of the waiting period.';
        return CustomerServiceWeekSms::appreciate($message);
    }

    public function send(int $memberId, ?string $inviteLink = null): void
    {
        $db = Database::getInstance();
        $connection = $db->getConnection();
        $ownTransaction = !$connection->inTransaction();
        $queueId = null;
        if ($ownTransaction) $connection->beginTransaction();
        else $db->query('SAVEPOINT registration_sms');
        try {
            // Serialize all activation/invitation paths for this member.
            $member = $db->fetch('SELECT m.*, u.first_name, u.phone FROM members m JOIN users u ON u.id = m.user_id WHERE m.id = :id FOR UPDATE', ['id'=>$memberId]);
            if (!$member) throw new RuntimeException('Member not found for registration notification.');
            $event = $db->fetch('SELECT member_id FROM registration_sms_events WHERE member_id = :id', ['id'=>$memberId]);
            if (!$event) {
                $message = self::message($member, (new PlatinumBillingService())->accountSummary($member), $inviteLink);
                if (SmsTriggerContext::isStaffAction()) {
                    $draftId = (new SmsReviewService())->create($member['phone'], $message, ['source'=>'Registration welcome', 'key'=>'registration:'.$memberId]);
                    $db->insert('registration_sms_events', ['member_id'=>$memberId, 'draft_id'=>$draftId, 'origin'=>'staff']);
                } else {
                    // Persist before transport; a failed/unknown gateway result must not create a second welcome.
                    $phone = SmsReviewService::phone((string)$member['phone']);
                    SmsReviewService::validateMessage($message);
                    $queueId = $db->insert('sms_queue', ['phone_number'=>$phone, 'message'=>$message, 'priority'=>'normal', 'status'=>'pending']);
                    $db->insert('registration_sms_events', ['member_id'=>$memberId, 'queue_id'=>$queueId, 'origin'=>'automatic']);
                }
            }
            if ($ownTransaction) $connection->commit();
            else $db->query('RELEASE SAVEPOINT registration_sms');
        } catch (Throwable $e) {
            if ($ownTransaction && $connection->inTransaction()) $connection->rollBack();
            elseif (!$ownTransaction) $db->query('ROLLBACK TO SAVEPOINT registration_sms');
            throw $e;
        }
        // Nested payment transactions leave the durable queue for the existing SMS worker after commit.
        if ($ownTransaction && $queueId) {
            try { (new BulkSmsService())->processQueueByIds([(int)$queueId]); }
            catch (Throwable $e) { error_log('Registration SMS queued for retry: ' . $e->getMessage()); }
        }
    }
}
