-- Additive schema only. Apply before deploying the shared business-SMS review flow.
-- Existing member, coverage, payment and SMS history rows are not changed.
CREATE TABLE IF NOT EXISTS sms_review_drafts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    source VARCHAR(160) NOT NULL DEFAULT 'Business notification',
    open_key CHAR(64) NULL,
    created_by BIGINT NULL,
    status ENUM('draft','queued','discarded') NOT NULL DEFAULT 'draft',
    version INT UNSIGNED NOT NULL DEFAULT 1,
    sms_queue_id BIGINT NULL,
    reviewed_by BIGINT NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_sms_review_open (open_key),
    KEY idx_sms_review_phone (phone_number, id),
    KEY idx_sms_review_actor (status, created_by, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
