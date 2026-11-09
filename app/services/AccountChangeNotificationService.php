<?php
require_once __DIR__ . "/SmsReviewService.php";

class AccountChangeNotificationService
{
    public function preview(array $member): array
    {
        $db = Database::getInstance();
        $breakdown = (new PlatinumBillingService())->accountBreakdown($member);
        $key = PlatinumPricingService::resolvePackageKey($member);
        $package = $GLOBALS['membership_packages'][$key]['name'] ?? null;
        if (!$package) throw new RuntimeException('Choose an exact Basic package before preparing an SMS.');

        $groups = $db->fetchAll("SELECT id, label, package_key FROM member_corporate_members WHERE member_id = :id AND status = 'active' ORDER BY id", ['id' => $member['id']]);
        $coverages = $db->fetchAll("SELECT * FROM platinum_coverages WHERE member_id = :id AND (status = 'active' OR (status = 'pending_approval' AND registration_selected = 1)) ORDER BY id", ['id' => $member['id']]);
        $byOwner = [];
        foreach ($coverages as $coverage) {
            $owner = $coverage['covered_person_type'] . ':' . (int)($coverage['covered_person_id'] ?? 0);
            if (isset($byOwner[$owner])) throw new RuntimeException('Review duplicate Platinum coverage before sending an account update.');
            $byOwner[$owner] = $coverage;
        }
        $name = preg_split('/\s+/u', trim((string)($member['first_name'] ?? '')))[0] ?? '';
        $greeting = $name !== '' ? 'Hi ' . $name : 'Hello';
        $household = 'you';
        if (str_starts_with($key, 'couple_children_parents_inlaws_')) $household = 'you, your spouse, children, parents and parents-in-law';
        elseif (str_starts_with($key, 'couple_children_parents_')) $household = 'you, your spouse, children and parents';
        elseif (str_starts_with($key, 'couple_children_')) $household = 'you, your spouse and children';
        elseif (str_starts_with($key, 'couple_')) $household = 'you and your spouse';
        $parts = [$greeting . ', your SHENA plan for ' . $household . ' is now ' . $breakdown['principal_tier'] . '.'];
        $ownerLabels = ['principal:0' => 'Your'];
        foreach ($groups as $group) {
            $owner = 'corporate_member:' . $group['id'];
            $tier = isset($byOwner[$owner]) ? 'Platinum' : 'Basic';
            $groupPackage = $GLOBALS['membership_packages'][$group['package_key']]['name'] ?? null;
            if (!$groupPackage) throw new RuntimeException('Review the additional member package before preparing an SMS.');
            $groupName = preg_split('/\s+/u', trim((string)$group['label']))[0] ?: 'The additional member';
            $parts[] = $groupName . ' is on a separate ' . $tier . ' plan.';
            $ownerLabels[$owner] = $groupName . "'s";
        }
        $parts[] = 'Your total monthly payment is KES ' . number_format($breakdown['total'], 2)
            . '.';
        foreach ($coverages as $coverage) {
            if (($coverage['status'] ?? '') === 'pending_approval') {
                $parts[] = 'Platinum benefits require approval and completion of the waiting period.';
                break;
            }
        }
        foreach ($coverages as $coverage) {
            if (!empty($coverage['maturity_date']) && $coverage['maturity_date'] > date('Y-m-d')) {
                $owner = $coverage['covered_person_type'] . ':' . (int)($coverage['covered_person_id'] ?? 0);
                $label = $ownerLabels[$owner] ?? "The additional member's";
                $parts[] = $label . ' waiting period for Platinum hospital benefits ends on ' . date('j M Y', strtotime($coverage['maturity_date'])) . '.';
            }
        }
        $change = $_SESSION['account_sms_pending'][(int)$member['id']] ?? [];
        $change = is_array($change) ? $change : [];
        $action = $change['type'] ?? 'conversion';
        if ($action !== 'conversion') {
            $parts = [];
            if ($action === 'package_update') {
                $oldPackage = $GLOBALS['membership_packages'][$change['old_package'] ?? '']['name'] ?? null;
                $parts[] = $greeting . ', we have changed your SHENA package'
                    . ($oldPackage ? ' from ' . $oldPackage : '') . ' to ' . $package . '.';
            } elseif ($action === 'household_update') {
                $parts[] = $greeting . ', we have updated the additional members and their plans on your SHENA account.';
                foreach ($groups as $group) {
                    $tier = isset($byOwner['corporate_member:' . $group['id']]) ? 'Platinum' : 'Basic';
                    $parts[] = $group['label'] . ': ' . $tier . ', ' . $GLOBALS['membership_packages'][$group['package_key']]['name'] . '.';
                }
            } elseif ($action !== 'amount_update') {
                $parts[] = $greeting . ', your SHENA account details have been updated.';
            }
            $amountChanged = isset($change['old_amount']) && abs((float)$change['old_amount'] - (float)$breakdown['total']) >= 0.01;
            $payment = $amountChanged
                ? 'Your monthly payment has changed from KES ' . number_format((float)$change['old_amount'], 2) . ' to KES ' . number_format($breakdown['total'], 2) . '.'
                : 'Your total monthly payment is KES ' . number_format($breakdown['total'], 2) . '.';
            $parts[] = $action === 'amount_update' ? $greeting . ', ' . lcfirst($payment) : $payment;
        }
        $message = implode(' ', $parts);
        $phone = preg_replace('/[^0-9]/', '', (string)($member['phone'] ?? ''));
        if (preg_match('/^0([17]\d{8})$/', $phone, $m)) $phone = '254' . $m[1];
        if (preg_match('/^[17]\d{8}$/', $phone)) $phone = '254' . $phone;
        if (!preg_match('/^254[17]\d{8}$/', $phone)) throw new RuntimeException('Update the member phone number before sending an SMS.');
        return ['message' => $message, 'phone' => $phone, 'token' => hash('sha256', json_encode([$member['id'], $phone, $message, $key, $groups, $coverages, $change]))];
    }

    public function queue(array $member, string $reviewedToken, ?string $editedMessage = null): int
    {
        $db = Database::getInstance();
        $preview = $this->preview($member);
        if (!hash_equals($preview['token'], $reviewedToken)) throw new RuntimeException('The account changed after this preview. Review the latest SMS before sending.');
        $preview['message'] = SmsReviewService::validateMessage($editedMessage ?? $preview['message']);
        $existing = $db->fetch("SELECT id, message, status, phone_number FROM sms_queue WHERE user_id = :user AND bulk_message_id IS NULL AND phone_number = :phone ORDER BY id DESC LIMIT 1", ['user' => $member['user_id'], 'phone' => $preview['phone']]);
        if ($existing && $existing['message'] === $preview['message'] && $existing['phone_number'] === $preview['phone'] && $existing['status'] !== 'failed') throw new RuntimeException('This account update is already queued or was submitted. Check SMS history before sending again.');
        $recent = $db->fetch("SELECT id FROM sms_queue WHERE user_id = :user AND bulk_message_id IS NULL AND phone_number = :phone AND status <> 'failed' AND (status = 'pending' OR created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)) ORDER BY id DESC LIMIT 1", ['user' => $member['user_id'], 'phone' => $preview['phone']]);
        if ($recent) throw new RuntimeException('An account update is pending or was queued in the last 10 minutes. Finish your corrections and check SMS history before notifying the member again.');
        return (int)$db->insert('sms_queue', ['phone_number' => $preview['phone'], 'message' => $preview['message'], 'priority' => 'normal', 'status' => 'pending', 'user_id' => $member['user_id']]);
    }
}
