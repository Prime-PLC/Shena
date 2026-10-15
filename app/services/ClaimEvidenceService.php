<?php
/** Shared document policy for member and administrator submissions. */
class ClaimEvidenceService
{
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }
    public static function required(string $type): array
    {
        if ($type === 'funeral') return ['id_copy' => 'ID or birth certificate', 'chief_letter' => "Chief's letter", 'mortuary_invoice' => 'Mortuary invoice'];
        if ($type === 'inpatient') return ['admission_proof' => 'Admission proof showing patient, hospital and admission date'];
        throw new InvalidArgumentException('Unknown claim type.');
    }
    public static function deadline(string $type, array $case): DateTimeImmutable
    {
        self::required($type);
        $zone = new DateTimeZone('Africa/Nairobi');
        return $type === 'funeral'
            ? (new DateTimeImmutable($case['created_at'], $zone))->modify('+7 days')
            : new DateTimeImmutable(substr($case['admission_date'], 0, 10) . ' 23:59:59', $zone);
    }
    public function find(string $type, int $id): array
    {
        self::required($type);
        $table = $type === 'funeral' ? 'claims' : 'inpatient_requests';
        $case = $this->db->fetch("SELECT * FROM {$table} WHERE id = :id", ['id' => $id]);
        if (!$case) throw new RuntimeException('Claim not found.');
        return $case;
    }
    public function documents(string $type, int $id): array
    {
        if ($type === 'funeral') return (new ClaimDocument())->getClaimDocuments($id);
        $docs = $this->db->fetchAll('SELECT * FROM claim_evidence WHERE case_type = :type AND case_id = :id ORDER BY created_at', ['type' => $type, 'id' => $id]);
        return $docs;
    }
    public function review(string $type, int $id): array
    {
        return $this->db->fetch('SELECT * FROM claim_evidence_reviews WHERE case_type = :type AND case_id = :id', ['type' => $type, 'id' => $id]) ?: [];
    }
    public static function missing(string $type, array $docs): array
    {
        return array_diff_key(self::required($type), array_flip(array_column($docs, 'document_type')));
    }
    public static function submittedOnTime(string $type, array $case, array $docs): bool
    {
        $onTime = array_filter($docs, function ($doc) use ($type, $case) {
            return !empty($doc['created_at']) && new DateTimeImmutable($doc['created_at'], new DateTimeZone('Africa/Nairobi')) <= self::deadline($type, $case);
        });
        return !self::missing($type, $onTime);
    }
    public function assertReady(string $type, int $id): void
    {
        $case = $this->find($type, $id);
        $docs = $this->documents($type, $id);
        $review = $this->review($type, $id);
        $missing = self::missing($type, $docs);
        if ($missing) throw new RuntimeException('Upload required documents: ' . implode(', ', $missing) . '. Open the claim documents page.');
        if ($type === 'funeral' && empty($review['accepted_at']) && !in_array($case['status'], ['approved', 'processed', 'completed', 'paid'], true)) {
            throw new RuntimeException('Accept and verify the initial application on the claim documents page first.');
        }
        if (!self::submittedOnTime($type, $case, $docs) && empty($review['exception_reason'])) {
            throw new RuntimeException('Documents were submitted after the deadline. Record the reason for an administrator-authorized deadline exception.');
        }
    }
    public function recordReview(string $type, int $id, int $userId, string $action, string $reason): void
    {
        $case = $this->find($type, $id);
        if (!in_array($case['status'], ['submitted', 'under_review', 'approved', 'partially_approved', 'processed'], true)) throw new RuntimeException('This claim is closed.');
        if ($action === 'accept' && $type === 'funeral') {
            if (!in_array($case['status'], ['submitted', 'under_review'], true)) throw new RuntimeException('Claim has already been approved.');
            $this->db->query('INSERT INTO claim_evidence_reviews (case_type, case_id, accepted_at, accepted_by) VALUES (:type, :id, NOW(), :user) ON DUPLICATE KEY UPDATE accepted_at = COALESCE(accepted_at, NOW()), accepted_by = COALESCE(accepted_by, VALUES(accepted_by))', ['type'=>$type, 'id'=>$id, 'user'=>$userId]);
            $this->db->update('claims', ['status'=>'under_review'], "id = :id AND status IN ('submitted', 'under_review')", ['id'=>$id]);
        } elseif ($action === 'exception') {
            if (strlen(trim($reason)) < 15) throw new RuntimeException('Record the contact, circumstances and reason for allowing late documents (at least 15 characters).');
            $this->db->query('INSERT INTO claim_evidence_reviews (case_type, case_id, exception_reason, exception_by, exception_at) VALUES (:type, :id, :reason, :user, NOW()) ON DUPLICATE KEY UPDATE exception_reason = COALESCE(exception_reason, VALUES(exception_reason)), exception_by = COALESCE(exception_by, VALUES(exception_by)), exception_at = COALESCE(exception_at, NOW())', ['type'=>$type, 'id'=>$id, 'reason'=>trim($reason), 'user'=>$userId]);
        } else throw new RuntimeException('Invalid review action.');
    }
    public static function directory(): string { return ROOT_PATH . '/storage/private/claim-evidence'; }
    public function upload(string $type, int $id, string $documentType, array $file, int $userId): void
    {
        $case = $this->find($type, $id);
        if (in_array($case['status'], ['rejected', 'completed', 'paid', 'cancelled'], true)) throw new RuntimeException('This claim is closed.');
        if (!isset(self::required($type)[$documentType]) && !($type === 'funeral' && $documentType === 'death_certificate')) throw new RuntimeException('Invalid document type.');
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) throw new RuntimeException('Select a valid uploaded document.');
        if (filesize($file['tmp_name']) > 5 * 1024 * 1024) throw new RuntimeException('Maximum document size is 5 MB.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['application/pdf'=>['pdf'], 'image/jpeg'=>['jpg','jpeg'], 'image/png'=>['png']];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset($extensions[$mime]) || !in_array($ext, $extensions[$mime], true)) throw new RuntimeException('Use a PDF, JPG or PNG file with matching content.');
        $dir = self::directory();
        if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) throw new RuntimeException('Document storage unavailable.');
        $name = bin2hex(random_bytes(24)) . '.bin';
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) throw new RuntimeException('Could not store document.');
        chmod($dir . '/' . $name, 0600);
        try {
            $this->db->insert('claim_evidence', ['case_type'=>$type, 'case_id'=>$id, 'document_type'=>$documentType, 'storage_name'=>$name, 'file_name'=>substr(basename($file['name']),0,255), 'mime_type'=>$mime, 'uploaded_by'=>$userId, 'created_at'=>date('Y-m-d H:i:s')]);
        } catch (Throwable $e) { unlink($dir . '/' . $name); throw $e; }
    }
}
