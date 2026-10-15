<?php
class ClaimEvidenceController extends BaseController
{
    private function authorize(string $type, int $id): array
    {
        $this->requireAuth();
        $case = (new ClaimEvidenceService())->find($type, $id);
        $admin = in_array($_SESSION['user_role'] ?? '', ['super_admin', 'manager'], true);
        $member = (new Member())->findByUserId($_SESSION['user_id']);
        if (!$admin && (!$member || (int)$case['member_id'] !== (int)$member['id'])) {
            http_response_code(403); exit('Access denied.');
        }
        return [$case, $admin];
    }
    public function show($type, $id)
    {
        [$case, $admin] = $this->authorize($type, (int)$id);
        $service = new ClaimEvidenceService();
        $this->view('claim-evidence', ['case'=>$case, 'admin'=>$admin, 'type'=>$type, 'id'=>(int)$id, 'docs'=>$service->documents($type,(int)$id), 'review'=>$service->review($type,(int)$id), 'csrf'=>$this->generateCsrfToken()]);
    }
    public function save($type, $id)
    {
        [$case, $admin] = $this->authorize($type, (int)$id);
        try {
            $this->validateCsrf();
            $service = new ClaimEvidenceService();
            $action = $_POST['action'] ?? 'upload';
            if ($action === 'upload') $service->upload($type,(int)$id,(string)($_POST['document_type'] ?? ''),$_FILES['document'] ?? [],(int)$_SESSION['user_id']);
            else {
                if (!$admin) throw new RuntimeException('Administrator access required.');
                $service->recordReview($type,(int)$id,(int)$_SESSION['user_id'],$action,(string)($_POST['reason'] ?? ''));
                if ($action === 'accept') {
                    try {
                        $member = (new Member())->find($case['member_id']);
                        $due = ClaimEvidenceService::deadline($type, $case)->format('d M Y H:i');
                        (new InAppNotificationService())->notifyUser($member['user_id'], [
                            'subject'=>'Claim application accepted for document review',
                            'message'=>"Claim #{$id} has been verified for document review. Upload the required proof by {$due} EAT. This is seven days from filing; final approval requires all documents.",
                            'action_url'=>'/claim-documents/funeral/' . (int)$id,
                            'action_text'=>'Upload documents'
                        ], $_SESSION['user_id']);
                    } catch (Throwable $e) { error_log('Claim acceptance notification failed: ' . $e->getMessage()); }
                }
            }
            $_SESSION['evidence_message'] = 'Saved. Final processing requires all proof documents.';
        } catch (Exception $e) { $_SESSION['evidence_message'] = $e->getMessage(); }
        $this->redirect('/claim-documents/' . $type . '/' . (int)$id);
    }
    public function download($type, $id, $documentId)
    {
        $this->authorize($type, (int)$id);
        $doc = $this->db->fetch('SELECT * FROM claim_evidence WHERE id = :doc AND case_type = :type AND case_id = :id', ['doc'=>(int)$documentId,'type'=>$type,'id'=>(int)$id]);
        if (!$doc || !preg_match('/^[a-f0-9]{48}\.bin$/D', $doc['storage_name'])) { http_response_code(404); exit; }
        $path = ClaimEvidenceService::directory() . '/' . $doc['storage_name'];
        if (!is_file($path)) { http_response_code(404); exit; }
        header('Content-Type: ' . $doc['mime_type']);
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header('Content-Disposition: attachment; filename="document.' . ['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png'][$doc['mime_type']] . '"');
        readfile($path); exit;
    }
}
