-- SHENA Platinum foundation
-- Basic membership tables remain unchanged.

CREATE TABLE IF NOT EXISTS platinum_coverages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    covered_person_type ENUM('principal', 'dependent', 'corporate_member') NOT NULL,
    covered_person_id INT NULL,
    status ENUM('pending_payment', 'pending_approval', 'active', 'rejected', 'suspended', 'expired', 'cancelled') NOT NULL DEFAULT 'pending_payment',
    monthly_contribution DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    maturity_months TINYINT UNSIGNED NOT NULL DEFAULT 4,
    requested_at DATETIME NULL,
    approved_at DATETIME NULL,
    approved_by INT NULL,
    effective_from DATE NULL,
    maturity_date DATE NULL,
    coverage_ends_at DATE NULL,
    principal_unique_key TINYINT GENERATED ALWAYS AS (CASE WHEN covered_person_type = 'principal' THEN 1 ELSE NULL END) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_platinum_coverages_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    CONSTRAINT fk_platinum_coverages_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_platinum_principal (member_id, principal_unique_key),
    UNIQUE KEY uq_platinum_person (member_id, covered_person_type, covered_person_id),
    INDEX idx_platinum_status (status),
    INDEX idx_platinum_maturity (maturity_date),
    INDEX idx_platinum_person (covered_person_type, covered_person_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS platinum_day_ledgers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    platinum_coverage_id INT UNSIGNED NOT NULL,
    calendar_year YEAR NOT NULL,
    days_reserved TINYINT UNSIGNED NOT NULL DEFAULT 0,
    days_used TINYINT UNSIGNED NOT NULL DEFAULT 0,
    annual_limit TINYINT UNSIGNED NOT NULL DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_platinum_ledger_coverage FOREIGN KEY (platinum_coverage_id) REFERENCES platinum_coverages(id) ON DELETE CASCADE,
    UNIQUE KEY uq_platinum_ledger_year (platinum_coverage_id, calendar_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inpatient_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    platinum_coverage_id INT UNSIGNED NOT NULL,
    covered_person_type ENUM('principal', 'dependent', 'corporate_member') NOT NULL,
    covered_person_id INT NULL,
    patient_name VARCHAR(200) NOT NULL,
    facility_name VARCHAR(200) NOT NULL,
    facility_location VARCHAR(255) NOT NULL,
    facility_contact VARCHAR(100) NULL,
    admission_date DATE NOT NULL,
    requested_days TINYINT UNSIGNED NOT NULL,
    approved_days TINYINT UNSIGNED NULL,
    admission_reference VARCHAR(150) NULL,
    status ENUM('submitted', 'under_review', 'approved', 'partially_approved', 'rejected', 'cancelled', 'completed') NOT NULL DEFAULT 'submitted',
    rejection_reason TEXT NULL,
    admin_notes TEXT NULL,
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inpatient_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    CONSTRAINT fk_inpatient_coverage FOREIGN KEY (platinum_coverage_id) REFERENCES platinum_coverages(id) ON DELETE RESTRICT,
    CONSTRAINT fk_inpatient_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_inpatient_status (status),
    INDEX idx_inpatient_member (member_id),
    INDEX idx_inpatient_admission (admission_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;