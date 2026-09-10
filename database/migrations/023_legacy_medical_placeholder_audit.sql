-- Creates a rollback/audit store for the separately reviewed conversion of
-- legacy "medical" corporate placeholders into principal Platinum coverages.
-- This migration deliberately does not move or delete member data.
CREATE TABLE IF NOT EXISTS legacy_medical_corporate_archive (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    legacy_corporate_member_id INT NOT NULL,
    member_id INT NOT NULL,
    label VARCHAR(200) NOT NULL,
    relationship VARCHAR(100) NULL,
    date_of_birth DATE NULL,
    package_key VARCHAR(100) NULL,
    package_name VARCHAR(200) NULL,
    monthly_contribution DECIMAL(10,2) NULL,
    status VARCHAR(30) NULL,
    archived_at DATETIME NOT NULL,
    UNIQUE KEY uq_legacy_medical_corporate_member (legacy_corporate_member_id),
    KEY idx_legacy_medical_member (member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
