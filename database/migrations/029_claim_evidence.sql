-- Apply before deploying the claim evidence workflow. Existing documents remain valid.
CREATE TABLE IF NOT EXISTS claim_evidence_reviews (
 case_type VARCHAR(16) NOT NULL, case_id INT NOT NULL,
 accepted_at DATETIME NULL, accepted_by INT NULL,
 exception_reason TEXT NULL, exception_by INT NULL, exception_at DATETIME NULL,
 PRIMARY KEY (case_type, case_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS claim_evidence (
 id INT AUTO_INCREMENT PRIMARY KEY, case_type VARCHAR(16) NOT NULL, case_id INT NOT NULL,
 document_type VARCHAR(40) NOT NULL, storage_name VARCHAR(80) NOT NULL,
 file_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL,
 uploaded_by INT NOT NULL, created_at DATETIME NOT NULL,
 INDEX evidence_case (case_type, case_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
