-- SHENA Platinum payment integration
-- Links M-Pesa payments to platinum coverage requests/renewals and records
-- the last confirmed payment reference on the coverage itself.

ALTER TABLE payments
    ADD COLUMN platinum_coverage_id INT UNSIGNED NULL COMMENT 'Linked platinum_coverages.id for platinum contribution payments' AFTER member_id;

ALTER TABLE payments
    MODIFY COLUMN payment_type ENUM('monthly', 'registration', 'reactivation', 'upgrade', 'penalty', 'platinum', 'other')
    DEFAULT 'monthly';

CREATE INDEX idx_payments_platinum_coverage ON payments(platinum_coverage_id);

ALTER TABLE payments
    ADD CONSTRAINT fk_payments_platinum_coverage FOREIGN KEY (platinum_coverage_id) REFERENCES platinum_coverages(id) ON DELETE SET NULL;

ALTER TABLE platinum_coverages
    ADD COLUMN payment_reference VARCHAR(100) NULL AFTER coverage_ends_at,
    ADD COLUMN last_payment_at DATETIME NULL AFTER payment_reference;
