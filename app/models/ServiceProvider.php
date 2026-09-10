<?php
require_once __DIR__ . '/../services/SmsReviewService.php';

/** Provider directory and stage assignments; caller owns transactions for mutations. */
class ServiceProvider extends BaseModel
{
    protected $table = 'service_providers';
    public const STAGES = [
        'mortuary_bill' => 'Mortuary bill', 'body_dressing' => 'Body dressing',
        'coffin' => 'Coffin', 'transportation' => 'Transportation',
        'equipment' => 'Funeral equipment', 'platinum_hospital' => 'Platinum hospital care',
    ];

    public static function validateDetails(array $input): array
    {
        $stage = (string)($input['stage_key'] ?? '');
        if (!isset(self::STAGES[$stage])) throw new RuntimeException('Select a valid service stage.');
        $values = ['stage_key' => $stage];
        foreach (['contact_name'=>120, 'business_name'=>180, 'email'=>190, 'address'=>255] as $field=>$limit) {
            $value = trim((string)($input[$field] ?? ''));
            if (preg_match_all('/./us', $value) === false || preg_match_all('/./us', $value) > $limit) throw new RuntimeException('The provider details are too long or contain invalid text.');
            $values[$field] = $value;
        }
        if ($values['contact_name'] === '' && $values['business_name'] === '') throw new RuntimeException('Enter a contact name or a company, institution or business name.');
        if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email or leave it blank.');
        $identity = preg_replace('/\s+/u', ' ', $values['business_name'] ?: $values['contact_name']);
        $values['provider_identity'] = $identity;
        $values['phone'] = SmsReviewService::phone((string)($input['phone'] ?? ''));
        return $values;
    }

    public static function validateStage(string $type, string $stage, string $providerStage): void
    {
        $allowed = $type === 'funeral' ? array_diff(array_keys(self::STAGES), ['platinum_hospital']) : ($type === 'platinum' ? ['platinum_hospital'] : []);
        if (!in_array($stage, $allowed, true)) throw new RuntimeException('This provider belongs to a different service stage.');
    }

    public function directory(): array
    {
        return $this->db->fetchAll('SELECT * FROM service_providers ORDER BY active DESC, business_name, contact_name, id');
    }

    public function assignments(string $type, int $caseId): array
    {
        $rows = $this->db->fetchAll('SELECT a.*, p.contact_name, p.business_name, p.phone, p.email, p.active AS provider_active FROM claim_provider_assignments a LEFT JOIN service_providers p ON p.id=a.provider_id WHERE a.case_type=:type AND a.case_id=:case_id', ['type'=>$type,'case_id'=>$caseId]);
        return array_column($rows, null, 'stage_key');
    }

    private function audit(string $action, array $details, int $actor): void
    {
        $this->db->insert('activity_logs', ['user_id'=>$actor,'action'=>$action,'details'=>json_encode($details)]);
    }

    private function invalidateDraft(?int $draftId): void
    {
        if ($draftId) $this->db->query("UPDATE sms_review_drafts SET status='discarded', open_key=NULL, version=version+1 WHERE id=:id AND status='draft'", ['id'=>$draftId]);
    }

    public function saveProvider(int $id, array $input, int $actor, bool $remove = false): int
    {
        $old = $id ? $this->db->fetch('SELECT * FROM service_providers WHERE id=:id FOR UPDATE', ['id'=>$id]) : null;
        if ($id && !$old) throw new RuntimeException('Service provider not found.');
        if ($remove) {
            if (!$old) throw new RuntimeException('Select a provider to remove.');
            $values = ['active'=>0];
        } else {
            $values = self::validateDetails($input);
            $values['active'] = 1;
        }
        if ($id) {
            $links = $this->db->fetchAll('SELECT draft_id FROM claim_provider_assignments WHERE provider_id=:id ORDER BY id', ['id'=>$id]);
            foreach ($links as $link) $this->invalidateDraft($link['draft_id'] ? (int)$link['draft_id'] : null);
            $this->db->update($this->table, $values, 'id=:id', ['id'=>$id]);
        } else $id = (int)$this->db->insert($this->table, $values);
        $this->audit($remove ? 'provider_removed' : 'provider_saved', ['provider_id'=>$id,'before'=>$old,'after'=>$values], $actor);
        return $id;
    }

    private function lockCase(string $type, int $id, string $stage): array
    {
        self::validateStage($type, $stage, $stage);
        $table = $type === 'funeral' ? 'claims' : 'inpatient_requests';
        $case = $this->db->fetch("SELECT * FROM {$table} WHERE id=:id FOR UPDATE", ['id'=>$id]);
        if (!$case) throw new RuntimeException('Claim or hospital request not found.');
        if ($type === 'funeral') {
            $check = $this->db->fetch('SELECT id FROM claim_service_checklist WHERE claim_id=:id AND service_type=:stage', ['id'=>$id,'stage'=>$stage]);
            if (!$check) throw new RuntimeException('This service stage is not part of this claim.');
        }
        return $case;
    }

    public function assign(string $type, int $caseId, string $stage, int $providerId, int $actor): void
    {
        $this->lockCase($type, $caseId, $stage);
        if ($providerId) {
            $provider = $this->db->fetch('SELECT * FROM service_providers WHERE id=:id FOR UPDATE', ['id'=>$providerId]);
            if (!$provider || !$provider['active']) throw new RuntimeException('Select an active service provider.');
            self::validateStage($type, $stage, $provider['stage_key']);
        }
        $old = $this->db->fetch('SELECT * FROM claim_provider_assignments WHERE case_type=:type AND case_id=:case_id AND stage_key=:stage FOR UPDATE', ['type'=>$type,'case_id'=>$caseId,'stage'=>$stage]);
        if ($old && (int)$old['provider_id'] === $providerId) return;
        if ($old) $this->invalidateDraft($old['draft_id'] ? (int)$old['draft_id'] : null);
        $values = ['provider_id'=>$providerId ?: null, 'draft_id'=>null, 'assigned_by'=>$actor];
        if ($old) $this->db->update('claim_provider_assignments', $values, 'id=:id', ['id'=>$old['id']]);
        elseif ($providerId) $this->db->insert('claim_provider_assignments', $values+['case_type'=>$type,'case_id'=>$caseId,'stage_key'=>$stage]);
        $this->audit('claim_provider_assigned', ['case_type'=>$type,'case_id'=>$caseId,'stage'=>$stage,'before'=>$old['provider_id'] ?? null,'after'=>$providerId ?: null], $actor);
    }

    /** Called before SMS draft locks so assignment changes and sends share a lock order. */
    public function assertCurrentDraft(int $draftId): void
    {
        $assignment = $this->db->fetch('SELECT * FROM claim_provider_assignments WHERE draft_id=:draft_id', ['draft_id'=>$draftId]);
        if (!$assignment || !$assignment['provider_id']) throw new RuntimeException('The provider assignment changed. Prepare a new message from the claim.');
        $case = $this->lockCase($assignment['case_type'], (int)$assignment['case_id'], $assignment['stage_key']);
        if (in_array($case['status'] ?? '', ['rejected','cancelled'], true)) throw new RuntimeException('This request is closed. Nothing was sent.');
        $provider = $this->db->fetch('SELECT * FROM service_providers WHERE id=:id FOR UPDATE', ['id'=>$assignment['provider_id']]);
        if (!$provider || !$provider['active']) throw new RuntimeException('This provider was removed. Nothing was sent.');
        self::validateStage($assignment['case_type'], $assignment['stage_key'], $provider['stage_key']);
        $current = $this->db->fetch('SELECT * FROM claim_provider_assignments WHERE id=:id FOR UPDATE', ['id'=>$assignment['id']]);
        if (!$current || (int)$current['draft_id'] !== $draftId || (int)$current['provider_id'] !== (int)$assignment['provider_id']) throw new RuntimeException('The provider changed. Prepare a new message from the claim.');
    }

    public function contact(string $type, int $caseId, string $stage, int $actor): int
    {
        $case = $this->lockCase($type, $caseId, $stage);
        if (in_array($case['status'] ?? '', ['rejected', 'cancelled'], true)) throw new RuntimeException('This request is closed. Provider SMS cannot be prepared.');
        $assignment = $this->db->fetch('SELECT * FROM claim_provider_assignments WHERE case_type=:type AND case_id=:case_id AND stage_key=:stage FOR UPDATE', ['type'=>$type,'case_id'=>$caseId,'stage'=>$stage]);
        if (empty($assignment['provider_id'])) throw new RuntimeException('Select and save a provider before preparing an SMS.');
        $provider = $this->db->fetch('SELECT * FROM service_providers WHERE id=:id FOR UPDATE', ['id'=>$assignment['provider_id']]);
        if (!$provider || !$provider['active']) throw new RuntimeException('This provider is no longer active. Select another provider.');
        self::validateStage($type, $stage, $provider['stage_key']);
        if ($assignment['draft_id']) {
            $existing = $this->db->fetch('SELECT id, status FROM sms_review_drafts WHERE id=:id FOR UPDATE', ['id'=>$assignment['draft_id']]);
            if ($existing && $existing['status'] === 'draft') return (int)$existing['id'];
        }
        $name = $provider['contact_name'] ?: $provider['business_name'];
        $person = $type === 'funeral' ? ($case['deceased_name'] ?? '') : ($case['patient_name'] ?? '');
        $message = 'Hello ' . $name . ', SHENA requests your assistance with ' . strtolower(self::STAGES[$stage])
            . ($person !== '' ? ' for ' . $person : '') . '. Reference: ' . ($type === 'funeral' ? 'claim' : 'hospital request') . ' #' . $caseId
            . '. Please contact SHENA to confirm availability and make arrangements. Thank you.';
        $draftId = (new SmsReviewService())->create($provider['phone'], $message, ['source'=>'Service provider: '.self::STAGES[$stage], 'key'=>'provider-assignment:'.$assignment['id']]);
        $this->db->update('claim_provider_assignments', ['draft_id'=>$draftId], 'id=:id', ['id'=>$assignment['id']]);
        $this->audit('provider_sms_drafted', ['assignment_id'=>$assignment['id'],'draft_id'=>$draftId], $actor);
        return $draftId;
    }
}
