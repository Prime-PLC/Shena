-- Migration: Corporate member line-item billing
-- Required for accurate corporate package selection and account-level monthly totals.
-- Run against the production database selected in cPanel/phpMyAdmin.

CREATE TABLE IF NOT EXISTS member_corporate_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    label VARCHAR(200) NOT NULL,
    relationship VARCHAR(100) DEFAULT 'corporate',
    package_key VARCHAR(100) NOT NULL,
    package_name VARCHAR(200) DEFAULT NULL,
    monthly_contribution DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_member_corporate_members_member
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_member_corporate_members_member_id (member_id),
    INDEX idx_member_corporate_members_status (status),
    INDEX idx_member_corporate_members_package_key (package_key)
);
