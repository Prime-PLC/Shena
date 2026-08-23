-- SHENA Platinum: registration opt-in, admin overrides, and admin-created inpatient requests

ALTER TABLE platinum_coverages
    ADD COLUMN IF NOT EXISTS activation_method ENUM('member_request', 'admin_override', 'admin_direct') NOT NULL DEFAULT 'member_request' AFTER status,
    ADD COLUMN IF NOT EXISTS override_reason TEXT NULL AFTER coverage_ends_at,
    ADD COLUMN IF NOT EXISTS override_by INT NULL AFTER override_reason,
    ADD COLUMN IF NOT EXISTS override_at DATETIME NULL AFTER override_by;

ALTER TABLE inpatient_requests
    ADD COLUMN IF NOT EXISTS admin_created TINYINT(1) NOT NULL DEFAULT 0 AFTER member_id,
    ADD COLUMN IF NOT EXISTS created_by INT NULL AFTER admin_created,
    ADD COLUMN IF NOT EXISTS eligibility_override_reason TEXT NULL AFTER rejection_reason;
