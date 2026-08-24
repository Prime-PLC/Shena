-- Platinum now belongs to a selected Basic coverage group, not each dependant.
ALTER TABLE member_corporate_members ADD COLUMN date_of_birth DATE NULL AFTER relationship;

ALTER TABLE beneficiaries
    ADD COLUMN coverage_owner_type ENUM('principal', 'corporate_member') NOT NULL DEFAULT 'principal' AFTER member_id,
    ADD COLUMN coverage_owner_id INT NULL AFTER coverage_owner_type,
    ADD INDEX idx_beneficiaries_coverage_owner (member_id, coverage_owner_type, coverage_owner_id);

ALTER TABLE platinum_coverages
    ADD COLUMN package_key VARCHAR(100) NULL AFTER covered_person_id,
    ADD COLUMN package_name VARCHAR(200) NULL AFTER package_key,
    ADD INDEX idx_platinum_package_group (member_id, covered_person_type, covered_person_id, package_key);

CREATE TABLE platinum_payment_allocations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    platinum_coverage_id INT UNSIGNED NOT NULL,
    allocated_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    allocation_month DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_platinum_payment_allocation (payment_id, platinum_coverage_id),
    KEY idx_platinum_allocation_coverage_month (platinum_coverage_id, allocation_month),
    CONSTRAINT fk_platinum_allocation_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    CONSTRAINT fk_platinum_allocation_coverage FOREIGN KEY (platinum_coverage_id) REFERENCES platinum_coverages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preserve existing records and attach their current group package where possible.
UPDATE platinum_coverages pc
JOIN members m ON m.id = pc.member_id
SET pc.package_key = COALESCE(pc.package_key, m.package_key, m.package),
    pc.package_name = COALESCE(pc.package_name, m.package_key, m.package)
WHERE pc.covered_person_type = 'principal';

UPDATE platinum_coverages pc
JOIN member_corporate_members cm ON cm.id = pc.covered_person_id
SET pc.package_key = COALESCE(pc.package_key, cm.package_key),
    pc.package_name = COALESCE(pc.package_name, cm.package_name, cm.package_key)
WHERE pc.covered_person_type = 'corporate_member';
