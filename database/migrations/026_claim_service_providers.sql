-- Additive provider directory and claim-stage assignments. Requires migration 025.
-- Existing claims, members and SMS history are not rewritten.
CREATE TABLE IF NOT EXISTS service_providers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    stage_key VARCHAR(40) NOT NULL,
    contact_name VARCHAR(120) NOT NULL DEFAULT '',
    business_name VARCHAR(180) NOT NULL DEFAULT '',
    provider_identity VARCHAR(180) COLLATE utf8mb4_unicode_ci NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(190) NOT NULL DEFAULT '',
    address VARCHAR(255) NOT NULL DEFAULT '',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_provider_identity (provider_identity),
    UNIQUE KEY uq_provider_phone (phone),
    UNIQUE KEY uq_provider_stage (id, stage_key),
    KEY idx_provider_stage (stage_key, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS claim_provider_assignments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    case_type ENUM('funeral','platinum') NOT NULL,
    case_id BIGINT UNSIGNED NOT NULL,
    stage_key VARCHAR(40) NOT NULL,
    provider_id BIGINT UNSIGNED NULL,
    assigned_by BIGINT NOT NULL,
    draft_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_claim_stage (case_type, case_id, stage_key),
    UNIQUE KEY uq_claim_provider (case_type, case_id, provider_id),
    CONSTRAINT fk_assignment_provider_stage FOREIGN KEY (provider_id, stage_key) REFERENCES service_providers(id, stage_key),
    CONSTRAINT fk_assignment_draft FOREIGN KEY (draft_id) REFERENCES sms_review_drafts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
