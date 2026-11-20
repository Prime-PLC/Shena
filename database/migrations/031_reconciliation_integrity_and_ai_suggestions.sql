-- Migration 031: Reconciliation integrity + AI-assisted match suggestions
-- Date: 2026-11-18
--
-- PREFLIGHT (run first, must return zero rows before the UNIQUE index is added):
--   SELECT mpesa_receipt_number, COUNT(*) c FROM payments
--   WHERE mpesa_receipt_number IS NOT NULL AND mpesa_receipt_number <> ''
--   GROUP BY mpesa_receipt_number HAVING c > 1;
-- If rows are returned, resolve those duplicates manually before applying.

-- 1. Empty-string receipts would collide under a UNIQUE index; NULL is allowed to repeat.
UPDATE payments SET mpesa_receipt_number = NULL WHERE mpesa_receipt_number = '';

-- 2. One payment row per M-Pesa receipt (closes the select-then-insert race).
ALTER TABLE payments ADD UNIQUE INDEX uq_payments_mpesa_receipt (mpesa_receipt_number);

-- 3. Track failed callback processing so retries can be re-attempted instead of
--    being swallowed by the "already processed" check.
ALTER TABLE mpesa_c2b_callbacks ADD COLUMN attempts INT NOT NULL DEFAULT 0 AFTER processed;
ALTER TABLE mpesa_c2b_callbacks ADD COLUMN processing_error TEXT NULL AFTER attempts;

-- 4. AI suggestions are advisory only. Nothing in this table posts money.
CREATE TABLE IF NOT EXISTS payment_ai_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    suggested_member_id INT NULL COMMENT 'NULL when the model found no credible candidate',
    confidence DECIMAL(5,2) NULL COMMENT '0-100, capped server-side',
    rationale TEXT NULL,
    candidates_json MEDIUMTEXT NULL COMMENT 'Ranked candidate ids + local features (no raw PII)',
    model VARCHAR(100) NULL,
    status ENUM('suggested', 'accepted', 'rejected', 'error') NOT NULL DEFAULT 'suggested',
    error_message VARCHAR(255) NULL,
    requested_by INT NULL,
    resolved_by INT NULL,
    resolved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (suggested_member_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ai_payment (payment_id),
    INDEX idx_ai_status (status),
    INDEX idx_ai_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
