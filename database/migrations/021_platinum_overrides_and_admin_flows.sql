-- SHENA Platinum: registration opt-in, admin overrides, and admin-created inpatient requests

ALTER TABLE platinum_coverages
    ADD COLUMN activation_method ENUM('member_request', 'admin_override', 'admin_direct') NOT NULL DEFAULT 'member_request' AFTER status,
    ADD COLUMN override_reason TEXT NULL AFTER coverage_ends_at,
    ADD COLUMN override_by INT NULL AFTER override_reason,
    ADD COLUMN override_at DATETIME NULL AFTER override_by;

ALTER TABLE inpatient_requests
    ADD COLUMN admin_created TINYINT(1) NOT NULL DEFAULT 0 AFTER member_id,
    ADD COLUMN created_by INT NULL AFTER admin_created,
    ADD COLUMN eligibility_override_reason TEXT NULL AFTER rejection_reason;
