-- Run before deploying the registration/SMS change. Existing pending conversions
-- keep their existing billing behavior; only new registration selections are billable.
ALTER TABLE platinum_coverages ADD COLUMN registration_selected TINYINT(1) NOT NULL DEFAULT 0;
CREATE TABLE IF NOT EXISTS registration_sms_events (
 member_id INT NOT NULL PRIMARY KEY,
 draft_id BIGINT NULL,
 queue_id BIGINT NULL,
 origin VARCHAR(24) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Do not send a fresh welcome when an existing member is reactivated (no retrospective welcome campaign).
INSERT IGNORE INTO registration_sms_events (member_id, origin)
SELECT id, 'legacy_existing' FROM members;
