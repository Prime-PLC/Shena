-- =============================================================
-- Shena Companion Welfare Association -- PRODUCTION DATABASE
-- Generated : 2026-04-06 15:37:52
-- Usage     : Import via cPanel -> phpMyAdmin -> Import tab
--             Create an empty database first, then import.
-- IMPORTANT : Update REPLACE_WITH_* placeholders and YOURDOMAIN.COM
--             before importing into production.
-- =============================================================

SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;


-- --------------------------------------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_logs_user_id` (`user_id`),
  KEY `idx_activity_logs_created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `agent_commissions`;
CREATE TABLE `agent_commissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` int NOT NULL,
  `member_id` int NOT NULL,
  `payment_id` int DEFAULT NULL,
  `commission_type` enum('registration','monthly','renewal') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','paid','cancelled') DEFAULT 'pending',
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_agent_status` (`agent_id`,`status`),
  KEY `idx_payment` (`payment_id`),
  KEY `idx_dates` (`created_at`,`paid_at`),
  CONSTRAINT `agent_commissions_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_commissions_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_commissions_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `agents`;
CREATE TABLE `agents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `agent_number` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `national_id` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text,
  `county` varchar(50) DEFAULT NULL,
  `status` enum('active','suspended','inactive') DEFAULT 'active',
  `commission_rate` decimal(5,2) DEFAULT '10.00' COMMENT 'Percentage commission',
  `total_members` int DEFAULT '0',
  `total_commission` decimal(10,2) DEFAULT '0.00',
  `registration_date` date DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_number` (`agent_number`),
  UNIQUE KEY `national_id` (`national_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_agent_number` (`agent_number`),
  KEY `idx_status` (`status`),
  KEY `idx_phone` (`phone`),
  CONSTRAINT `agents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `beneficiaries`;
CREATE TABLE `beneficiaries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int NOT NULL,
  `full_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT '100.00',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_beneficiaries_member_id` (`member_id`),
  KEY `idx_beneficiaries_active` (`is_active`),
  CONSTRAINT `beneficiaries_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `bulk_message_recipients`;
CREATE TABLE `bulk_message_recipients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bulk_message_id` int NOT NULL,
  `user_id` int NOT NULL,
  `recipient_type` enum('email','sms') NOT NULL,
  `recipient_value` varchar(100) NOT NULL COMMENT 'Email address or phone number',
  `status` enum('pending','sent','failed','bounced','skipped') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text,
  `email_fallback_sent` tinyint(1) DEFAULT '0',
  `email_sent_at` datetime DEFAULT NULL,
  `delivery_method` enum('sms','email','failed') DEFAULT NULL,
  `provider_message_id` varchar(100) DEFAULT NULL,
  `provider_response` text,
  PRIMARY KEY (`id`),
  KEY `idx_bulk_status` (`bulk_message_id`,`status`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `bulk_message_recipients_ibfk_1` FOREIGN KEY (`bulk_message_id`) REFERENCES `bulk_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bulk_message_recipients_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `bulk_messages`;
CREATE TABLE `bulk_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `message_type` enum('sms','email','both') NOT NULL,
  `target_audience` enum('all_members','active','grace_period','defaulted','custom') NOT NULL,
  `custom_filters` json DEFAULT NULL COMMENT 'Custom filter criteria',
  `total_recipients` int DEFAULT '0',
  `sent_count` int DEFAULT '0',
  `failed_count` int DEFAULT '0',
  `status` enum('draft','scheduled','sending','paused','completed','failed','cancelled') DEFAULT 'draft',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`message_type`),
  KEY `idx_scheduled` (`scheduled_at`),
  CONSTRAINT `bulk_messages_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `claim_cash_alternative_agreements`;
CREATE TABLE `claim_cash_alternative_agreements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `claim_id` int NOT NULL,
  `reason_category` enum('security_risk','client_request','logistical_issue','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `detailed_reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_by` enum('company','client') COLLATE utf8mb4_unicode_ci NOT NULL,
  `agreement_signed` tinyint(1) DEFAULT '0',
  `signature_document_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT '20000.00',
  `payment_method` enum('mpesa','bank','cash') COLLATE utf8mb4_unicode_ci DEFAULT 'mpesa',
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `approved_by` int NOT NULL,
  `approved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `claim_id` (`claim_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_claim_agreement` (`claim_id`),
  CONSTRAINT `claim_cash_alternative_agreements_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE,
  CONSTRAINT `claim_cash_alternative_agreements_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `claim_documents`;
CREATE TABLE `claim_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `claim_id` int NOT NULL,
  `document_type` enum('id_copy','birth_certificate','chief_letter','mortuary_invoice','death_certificate') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `claim_id` (`claim_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `claim_documents_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE,
  CONSTRAINT `claim_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `claim_payouts`;
CREATE TABLE `claim_payouts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `claim_id` int NOT NULL,
  `payout_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('mpesa','bank_transfer','cheque','cash') COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_type` enum('mpesa','bank') COLLATE utf8mb4_unicode_ci DEFAULT 'mpesa',
  `account_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_holder_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `processed_by` int DEFAULT NULL,
  `status` enum('pending','processing','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `processing_notes` text COLLATE utf8mb4_unicode_ci,
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_claim_payouts_claim_id` (`claim_id`),
  KEY `idx_claim_payouts_status` (`status`),
  KEY `idx_claim_payouts_processed_by` (`processed_by`),
  CONSTRAINT `claim_payouts_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE,
  CONSTRAINT `claim_payouts_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `claim_service_checklist`;
CREATE TABLE `claim_service_checklist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `claim_id` int NOT NULL,
  `service_type` enum('mortuary_bill','body_dressing','coffin','transportation','equipment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `completed` tinyint(1) DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `completed_by` int DEFAULT NULL,
  `service_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `completed_by` (`completed_by`),
  KEY `idx_claim_service` (`claim_id`,`service_type`),
  KEY `idx_completed` (`completed`),
  CONSTRAINT `claim_service_checklist_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE,
  CONSTRAINT `claim_service_checklist_ibfk_2` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `claims`;
CREATE TABLE `claims` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int NOT NULL,
  `beneficiary_id` int DEFAULT NULL,
  `dependent_id` int DEFAULT NULL,
  `deceased_type` enum('member','dependent') COLLATE utf8mb4_unicode_ci DEFAULT 'member',
  `deceased_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deceased_id_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `date_of_death` date NOT NULL,
  `place_of_death` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cause_of_death` text COLLATE utf8mb4_unicode_ci,
  `mortuary_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mortuary_bill_amount` decimal(10,2) DEFAULT '0.00',
  `claim_amount` decimal(10,2) NOT NULL,
  `approved_amount` decimal(10,2) DEFAULT '0.00' COMMENT 'Only used for cash alternative',
  `status` enum('submitted','under_review','approved','services_arranged','completed','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'submitted',
  `processed_by` int DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `processing_notes` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `service_delivery_type` enum('standard_services','cash_alternative') COLLATE utf8mb4_unicode_ci DEFAULT 'standard_services',
  `cash_alternative_reason` text COLLATE utf8mb4_unicode_ci,
  `cash_alternative_agreement_signed` tinyint(1) DEFAULT '0',
  `cash_alternative_amount` decimal(10,2) DEFAULT '20000.00',
  `mortuary_bill_settled` tinyint(1) DEFAULT '0',
  `body_dressing_completed` tinyint(1) DEFAULT '0',
  `coffin_delivered` tinyint(1) DEFAULT '0',
  `transportation_arranged` tinyint(1) DEFAULT '0',
  `equipment_delivered` tinyint(1) DEFAULT '0',
  `services_delivery_date` date DEFAULT NULL,
  `mortuary_days_count` int DEFAULT '0',
  `mortuary_bill_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `beneficiary_id` (`beneficiary_id`),
  KEY `processed_by` (`processed_by`),
  KEY `idx_claims_member_id` (`member_id`),
  KEY `idx_claims_status` (`status`),
  KEY `idx_claims_date_of_death` (`date_of_death`),
  KEY `idx_claims_delivery_date` (`services_delivery_date`),
  KEY `idx_claims_mortuary_settled` (`mortuary_bill_settled`),
  CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`beneficiary_id`) REFERENCES `beneficiaries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `claims_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `communication_recipients`;
CREATE TABLE `communication_recipients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `communication_id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` enum('email','sms') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','sent','failed','delivered','read') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `communication_id` (`communication_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `communication_recipients_ibfk_1` FOREIGN KEY (`communication_id`) REFERENCES `communications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `communication_recipients_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `communications`;
CREATE TABLE `communications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int DEFAULT NULL,
  `recipient_id` int DEFAULT NULL,
  `recipient_type` enum('individual','all','package','status') COLLATE utf8mb4_unicode_ci DEFAULT 'individual',
  `recipient_criteria` json DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('email','sms','both') COLLATE utf8mb4_unicode_ci DEFAULT 'both',
  `status` enum('draft','sent','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_communications_sender` (`sender_id`),
  KEY `idx_communications_recipient` (`recipient_id`),
  KEY `idx_communications_status` (`status`),
  CONSTRAINT `communications_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `communications_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `dependents`;
CREATE TABLE `dependents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int NOT NULL,
  `full_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship` enum('spouse','child','parent','father_in_law','mother_in_law') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_certificate` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_covered` tinyint(1) DEFAULT '1',
  `coverage_start_date` date NOT NULL,
  `coverage_end_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dependents_member_id` (`member_id`),
  KEY `idx_dependents_relationship` (`relationship`),
  KEY `idx_dependents_is_covered` (`is_covered`),
  CONSTRAINT `dependents_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `financial_transactions`;
CREATE TABLE `financial_transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transaction_type` enum('payment','commission','refund','adjustment','upgrade') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `member_id` int DEFAULT NULL,
  `agent_id` int DEFAULT NULL,
  `payment_id` int DEFAULT NULL,
  `upgrade_request_id` int DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `description` text,
  `status` enum('pending','completed','failed','reversed') DEFAULT 'completed',
  `transaction_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`),
  KEY `upgrade_request_id` (`upgrade_request_id`),
  KEY `idx_transaction_type` (`transaction_type`),
  KEY `idx_member_id` (`member_id`),
  KEY `idx_agent_id` (`agent_id`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `financial_transactions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `financial_transactions_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `financial_transactions_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `financial_transactions_ibfk_4` FOREIGN KEY (`upgrade_request_id`) REFERENCES `plan_upgrade_requests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `agent_id` int DEFAULT NULL,
  `member_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `next_of_kin` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_of_kin_relationship` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_of_kin_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package` enum('individual','family','extended_family_1','extended_family_2','executive','couple','basic','premium') COLLATE utf8mb4_unicode_ci DEFAULT 'individual',
  `package_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthly_contribution` decimal(10,2) DEFAULT '0.00',
  `corporate_couple_count` tinyint unsigned DEFAULT '0',
  `status` enum('active','inactive','grace_period','defaulted','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'inactive',
  `maturity_ends` date DEFAULT NULL,
  `coverage_ends` date DEFAULT NULL,
  `grace_period_expires` timestamp NULL DEFAULT NULL,
  `reactivated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `payment_deadline` date DEFAULT NULL,
  `pending_payment_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_upgrade_date` date DEFAULT NULL COMMENT 'Date of last package upgrade',
  `upgrade_count` int DEFAULT '0' COMMENT 'Total number of upgrades',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_number` (`member_number`),
  UNIQUE KEY `id_number` (`id_number`),
  KEY `idx_members_user_id` (`user_id`),
  KEY `idx_members_member_number` (`member_number`),
  KEY `idx_members_id_number` (`id_number`),
  KEY `idx_members_file_number` (`file_number`),
  KEY `idx_members_status` (`status`),
  KEY `idx_members_package` (`package`),
  KEY `agent_id` (`agent_id`),
  KEY `idx_package` (`package`),
  KEY `idx_members_maturity_ends` (`maturity_ends`),
  KEY `idx_members_coverage_ends` (`coverage_ends`),
  CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `members_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `mpesa_c2b_callbacks`;
CREATE TABLE `mpesa_c2b_callbacks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transaction_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trans_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'M-Pesa Transaction ID',
  `trans_time` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trans_amount` decimal(10,2) NOT NULL,
  `business_short_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bill_ref_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Account number sent by customer',
  `invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_account_balance` decimal(15,2) DEFAULT NULL,
  `third_party_trans_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `msisdn` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Customer phone number',
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middle_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_callback` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full JSON callback data',
  `processed` tinyint(1) DEFAULT '0',
  `processed_at` datetime DEFAULT NULL,
  `payment_id` int DEFAULT NULL COMMENT 'Linked payment after reconciliation',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trans_id` (`trans_id`),
  KEY `payment_id` (`payment_id`),
  KEY `idx_trans_id` (`trans_id`),
  KEY `idx_bill_ref` (`bill_ref_number`),
  KEY `idx_msisdn` (`msisdn`),
  KEY `idx_processed` (`processed`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `mpesa_c2b_callbacks_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `mpesa_config`;
CREATE TABLE `mpesa_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `environment` enum('sandbox','production') DEFAULT 'sandbox',
  `consumer_key` varchar(255) NOT NULL,
  `consumer_secret` varchar(255) NOT NULL,
  `short_code` varchar(20) NOT NULL,
  `pass_key` varchar(255) NOT NULL,
  `callback_url` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `notification_logs`;
CREATE TABLE `notification_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` enum('sms','email','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('success','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Additional info like fallback reason',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_phone` (`phone`),
  KEY `idx_email` (`email`),
  KEY `idx_method_status` (`method`,`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `notification_preferences`;
CREATE TABLE `notification_preferences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `email_enabled` tinyint(1) DEFAULT '1',
  `sms_enabled` tinyint(1) DEFAULT '1',
  `payment_reminders` tinyint(1) DEFAULT '1',
  `grace_period_alerts` tinyint(1) DEFAULT '1',
  `claim_updates` tinyint(1) DEFAULT '1',
  `general_announcements` tinyint(1) DEFAULT '1',
  `promotional_messages` tinyint(1) DEFAULT '0',
  `preferred_language` varchar(10) DEFAULT 'en',
  `quiet_hours_start` time DEFAULT NULL,
  `quiet_hours_end` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_prefs` (`user_id`),
  CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_type` (`type`),
  KEY `idx_notifications_read` (`is_read`),
  KEY `idx_notifications_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `payment_reconciliation_log`;
CREATE TABLE `payment_reconciliation_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payment_id` int NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'matched, unmatched, manual_match, rejected',
  `previous_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matched_member_id` int DEFAULT NULL,
  `match_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'auto_id_number, auto_phone, manual',
  `confidence_score` decimal(5,2) DEFAULT NULL COMMENT 'Matching confidence 0-100',
  `reconciled_by` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reconciled_by` (`reconciled_by`),
  KEY `idx_payment_id` (`payment_id`),
  KEY `idx_matched_member` (`matched_member_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `payment_reconciliation_log_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_reconciliation_log_ibfk_2` FOREIGN KEY (`matched_member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_reconciliation_log_ibfk_3` FOREIGN KEY (`reconciled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `payment_reminders`;
CREATE TABLE `payment_reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int NOT NULL,
  `reminder_type` enum('upcoming','due','overdue','grace_period') NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `sent_via` enum('sms','email','both') NOT NULL,
  `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `sms_status` enum('sent','failed','delivered') DEFAULT NULL,
  `email_status` enum('sent','failed','delivered') DEFAULT NULL,
  `response_action` enum('paid','ignored','contacted') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_member_reminder` (`member_id`,`reminder_type`),
  KEY `idx_sent_at` (`sent_at`),
  CONSTRAINT `payment_reminders_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int DEFAULT NULL,
  `sender_phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `mpesa_receipt_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` enum('registration','monthly','reactivation','upgrade','penalty','other') COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `payment_method` enum('mpesa','bank','cash','cheque') COLLATE utf8mb4_unicode_ci DEFAULT 'mpesa',
  `paybill_account` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `reconciliation_status` enum('pending','matched','unmatched','manual') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `transaction_date` datetime DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `reconciled_at` datetime DEFAULT NULL,
  `reconciled_by` int DEFAULT NULL,
  `reconciliation_notes` text COLLATE utf8mb4_unicode_ci,
  `merchant_request_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'M-Pesa Merchant Request ID',
  `checkout_request_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'M-Pesa Checkout Request ID',
  `result_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'M-Pesa result code',
  `result_desc` text COLLATE utf8mb4_unicode_ci COMMENT 'M-Pesa result description',
  `auto_matched` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_payments_member_id` (`member_id`),
  KEY `idx_payments_status` (`status`),
  KEY `idx_payments_payment_date` (`payment_date`),
  KEY `idx_payments_transaction_id` (`transaction_id`),
  KEY `idx_reconciliation_status` (`reconciliation_status`),
  KEY `idx_mpesa_receipt` (`mpesa_receipt_number`),
  KEY `idx_sender_phone` (`sender_phone`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_payments_checkout_request` (`checkout_request_id`),
  KEY `idx_payments_merchant_request` (`merchant_request_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `payout_requests`;
CREATE TABLE `payout_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('mpesa','bank_transfer','cash') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mpesa',
  `payment_details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('requested','processing','paid','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'requested',
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int DEFAULT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `processed_by` (`processed_by`),
  KEY `idx_payout_requests_agent_id` (`agent_id`),
  KEY `idx_payout_requests_status` (`status`),
  KEY `idx_payout_requests_requested_at` (`requested_at`),
  CONSTRAINT `payout_requests_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payout_requests_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `plan_upgrade_history`;
CREATE TABLE `plan_upgrade_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int NOT NULL,
  `upgrade_request_id` int DEFAULT NULL,
  `from_package` enum('individual','family','extended_family_1','extended_family_2','executive','couple') NOT NULL,
  `to_package` enum('individual','family','extended_family_1','extended_family_2','executive','couple') NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('mpesa','bank','cash') NOT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `effective_date` date NOT NULL,
  `upgraded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `processed_by` int DEFAULT NULL COMMENT 'User ID who processed (for admin upgrades)',
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `upgrade_request_id` (`upgrade_request_id`),
  KEY `processed_by` (`processed_by`),
  KEY `idx_member` (`member_id`),
  KEY `idx_effective_date` (`effective_date`),
  KEY `idx_upgraded_at` (`upgraded_at`),
  CONSTRAINT `plan_upgrade_history_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plan_upgrade_history_ibfk_2` FOREIGN KEY (`upgrade_request_id`) REFERENCES `plan_upgrade_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plan_upgrade_history_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `plan_upgrade_requests`;
CREATE TABLE `plan_upgrade_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int NOT NULL,
  `from_package` enum('individual','family','extended_family_1','extended_family_2','executive','couple') NOT NULL,
  `to_package` enum('individual','family','extended_family_1','extended_family_2','executive','couple') NOT NULL,
  `current_monthly_fee` decimal(10,2) NOT NULL,
  `new_monthly_fee` decimal(10,2) NOT NULL,
  `prorated_amount` decimal(10,2) NOT NULL,
  `days_remaining` int NOT NULL,
  `status` enum('pending','payment_initiated','completed','failed','cancelled') DEFAULT 'pending',
  `payment_method` enum('mpesa','bank','cash') DEFAULT NULL,
  `mpesa_checkout_id` varchar(100) DEFAULT NULL,
  `mpesa_receipt_number` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `requested_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancellation_reason` text,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `idx_member_status` (`member_id`,`status`),
  KEY `idx_status` (`status`),
  KEY `idx_requested_at` (`requested_at`),
  CONSTRAINT `plan_upgrade_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `resource_downloads`;
CREATE TABLE `resource_downloads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resource_id` int NOT NULL,
  `user_id` int NOT NULL,
  `downloaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_downloads_resource` (`resource_id`),
  KEY `idx_downloads_user` (`user_id`),
  KEY `idx_downloads_date` (`downloaded_at`),
  CONSTRAINT `resource_downloads_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resource_downloads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `resources`;
CREATE TABLE `resources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('marketing_materials','training_documents','policy_documents','forms','other') COLLATE utf8mb4_unicode_ci DEFAULT 'other',
  `uploaded_by` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `download_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_resources_category` (`category`),
  KEY `idx_resources_active` (`is_active`),
  KEY `idx_resources_created` (`created_at`),
  CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `scheduled_campaigns`;
CREATE TABLE `scheduled_campaigns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `campaign_name` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `recipient_type` enum('all','active','inactive','by_package','custom') DEFAULT 'all',
  `recipient_filter` json DEFAULT NULL COMMENT 'Filter criteria for recipients',
  `total_recipients` int DEFAULT '0',
  `sent_count` int DEFAULT '0',
  `failed_count` int DEFAULT '0',
  `scheduled_at` datetime NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
  `created_by` int NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `error_message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status_scheduled` (`status`,`scheduled_at`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `scheduled_campaigns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('boolean','string','integer','json') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `description` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `sms_credits`;
CREATE TABLE `sms_credits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `balance` int DEFAULT '0',
  `last_purchase_amount` int DEFAULT '0',
  `last_purchase_date` datetime DEFAULT NULL,
  `low_balance_threshold` int DEFAULT '100',
  `alert_sent` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `sms_queue`;
CREATE TABLE `sms_queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `status` enum('pending','processing','sent','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `retry_count` int DEFAULT '0',
  `max_retries` int DEFAULT '3',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `bulk_message_id` int DEFAULT NULL COMMENT 'Link to bulk campaign if applicable',
  `user_id` int DEFAULT NULL COMMENT 'User this SMS is for',
  `scheduled_at` datetime DEFAULT NULL COMMENT 'Send at specific time',
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_status_priority` (`status`,`priority`),
  KEY `idx_scheduled_at` (`scheduled_at`),
  KEY `idx_bulk_message` (`bulk_message_id`),
  CONSTRAINT `sms_queue_ibfk_1` FOREIGN KEY (`bulk_message_id`) REFERENCES `bulk_messages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sms_queue_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `sms_templates`;
CREATE TABLE `sms_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `template` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Template with {placeholders}',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'payment_reminder, claim_update, general, etc.',
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_category` (`category`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `sms_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `updated_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('member','agent','manager','admin','super_admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `status` enum('pending','active','inactive','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_phone` (`phone`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP VIEW IF EXISTS `vw_agent_leaderboard`;
CREATE OR REPLACE VIEW `vw_agent_leaderboard` AS select `a`.`id` AS `id`,`a`.`agent_number` AS `agent_number`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `agent_name`,`a`.`total_members` AS `total_members`,coalesce(sum((case when (`ac`.`status` = 'approved') then `ac`.`commission_amount` else 0 end)),0) AS `total_commissions_approved`,coalesce(sum((case when (`ac`.`status` = 'paid') then `ac`.`commission_amount` else 0 end)),0) AS `total_commissions_paid`,coalesce(sum((case when ((`ac`.`status` = 'paid') and (`ac`.`paid_at` >= (now() - interval 30 day))) then `ac`.`commission_amount` else 0 end)),0) AS `commissions_last_30_days`,`a`.`status` AS `status` from ((`agents` `a` join `users` `u` on((`a`.`user_id` = `u`.`id`))) left join `agent_commissions` `ac` on((`ac`.`agent_id` = `a`.`id`))) where (`a`.`status` = 'active') group by `a`.`id`,`a`.`agent_number`,`u`.`first_name`,`u`.`last_name`,`a`.`total_members`,`a`.`status` order by `total_commissions_paid` desc,`a`.`total_members` desc;

-- --------------------------------------------------------
DROP VIEW IF EXISTS `vw_campaign_stats`;
CREATE OR REPLACE VIEW `vw_campaign_stats` AS select `bm`.`id` AS `id`,`bm`.`title` AS `title`,`bm`.`message_type` AS `message_type`,`bm`.`status` AS `status`,`bm`.`scheduled_at` AS `scheduled_at`,`bm`.`total_recipients` AS `total_recipients`,`bm`.`sent_count` AS `sent_count`,`bm`.`failed_count` AS `failed_count`,((`bm`.`sent_count` / greatest(`bm`.`total_recipients`,1)) * 100) AS `success_rate`,count((case when (`bmr`.`status` = 'pending') then 1 end)) AS `pending_count`,`bm`.`created_at` AS `created_at`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `created_by_name` from ((`bulk_messages` `bm` left join `bulk_message_recipients` `bmr` on((`bm`.`id` = `bmr`.`bulk_message_id`))) left join `users` `u` on((`bm`.`created_by` = `u`.`id`))) group by `bm`.`id`;

-- --------------------------------------------------------
DROP VIEW IF EXISTS `vw_financial_summary`;
CREATE OR REPLACE VIEW `vw_financial_summary` AS select date_format(`financial_transactions`.`transaction_date`,'%Y-%m') AS `month`,sum((case when ((`financial_transactions`.`transaction_type` = 'payment') and (`financial_transactions`.`status` = 'completed')) then `financial_transactions`.`amount` else 0 end)) AS `total_payments`,sum((case when ((`financial_transactions`.`transaction_type` = 'commission') and (`financial_transactions`.`status` = 'completed')) then `financial_transactions`.`amount` else 0 end)) AS `total_commissions`,sum((case when ((`financial_transactions`.`transaction_type` = 'upgrade') and (`financial_transactions`.`status` = 'completed')) then `financial_transactions`.`amount` else 0 end)) AS `total_upgrades`,sum((case when ((`financial_transactions`.`transaction_type` = 'refund') and (`financial_transactions`.`status` = 'completed')) then `financial_transactions`.`amount` else 0 end)) AS `total_refunds`,count(distinct (case when (`financial_transactions`.`transaction_type` = 'payment') then `financial_transactions`.`member_id` end)) AS `paying_members`,count(distinct (case when (`financial_transactions`.`transaction_type` = 'commission') then `financial_transactions`.`agent_id` end)) AS `earning_agents` from `financial_transactions` where (`financial_transactions`.`status` = 'completed') group by date_format(`financial_transactions`.`transaction_date`,'%Y-%m') order by `month` desc;

-- --------------------------------------------------------
DROP VIEW IF EXISTS `vw_pending_reconciliation`;
CREATE OR REPLACE VIEW `vw_pending_reconciliation` AS select `c`.`id` AS `callback_id`,`c`.`trans_id` AS `trans_id`,`c`.`trans_amount` AS `trans_amount`,`c`.`trans_time` AS `trans_time`,`c`.`msisdn` AS `msisdn`,`c`.`bill_ref_number` AS `bill_ref_number`,concat(`c`.`first_name`,' ',coalesce(`c`.`middle_name`,''),' ',`c`.`last_name`) AS `sender_name`,`c`.`processed` AS `processed`,`c`.`created_at` AS `created_at`,`m`.`id` AS `potential_member_id`,`m`.`member_number` AS `member_number`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `member_name`,`u`.`phone` AS `member_phone`,(case when (`m`.`id_number` = `c`.`bill_ref_number`) then 'id_match' when (`u`.`phone` = `c`.`msisdn`) then 'phone_match' when (`m`.`member_number` = `c`.`bill_ref_number`) then 'member_number_match' else 'no_match' end) AS `match_type` from ((`mpesa_c2b_callbacks` `c` left join `members` `m` on(((`m`.`id_number` = `c`.`bill_ref_number`) or (`m`.`member_number` = `c`.`bill_ref_number`)))) left join `users` `u` on(((`m`.`user_id` = `u`.`id`) and (`u`.`phone` = `c`.`msisdn`)))) where (`c`.`processed` = false) order by `c`.`created_at` desc;

-- --------------------------------------------------------
DROP VIEW IF EXISTS `vw_pending_upgrades`;
CREATE OR REPLACE VIEW `vw_pending_upgrades` AS select `pur`.`id` AS `id`,`pur`.`member_id` AS `member_id`,`m`.`member_number` AS `member_number`,`u`.`first_name` AS `first_name`,`u`.`last_name` AS `last_name`,`u`.`phone` AS `phone`,`u`.`email` AS `email`,`pur`.`from_package` AS `from_package`,`pur`.`to_package` AS `to_package`,`pur`.`prorated_amount` AS `prorated_amount`,`pur`.`days_remaining` AS `days_remaining`,`pur`.`status` AS `status`,`pur`.`payment_method` AS `payment_method`,`pur`.`mpesa_receipt_number` AS `mpesa_receipt_number`,`pur`.`requested_at` AS `requested_at`,(to_days(now()) - to_days(`pur`.`requested_at`)) AS `days_pending` from ((`plan_upgrade_requests` `pur` join `members` `m` on((`pur`.`member_id` = `m`.`id`))) join `users` `u` on((`m`.`user_id` = `u`.`id`))) where (`pur`.`status` in ('pending','payment_initiated')) order by `pur`.`requested_at` desc;

-- --------------------------------------------------------
DROP VIEW IF EXISTS `vw_scheduled_campaigns_summary`;
CREATE OR REPLACE VIEW `vw_scheduled_campaigns_summary` AS select `sc`.`id` AS `id`,`sc`.`campaign_name` AS `campaign_name`,`sc`.`scheduled_at` AS `scheduled_at`,`sc`.`status` AS `status`,`sc`.`total_recipients` AS `total_recipients`,`sc`.`sent_count` AS `sent_count`,`sc`.`failed_count` AS `failed_count`,round(((`sc`.`sent_count` / nullif(`sc`.`total_recipients`,0)) * 100),2) AS `success_rate`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `created_by_name`,`sc`.`executed_at` AS `executed_at`,`sc`.`completed_at` AS `completed_at` from (`scheduled_campaigns` `sc` join `users` `u` on((`sc`.`created_by` = `u`.`id`))) order by `sc`.`scheduled_at` desc;

-- --------------------------------------------------------
DROP VIEW IF EXISTS `vw_sms_queue_status`;
CREATE OR REPLACE VIEW `vw_sms_queue_status` AS select cast(`sms_queue`.`created_at` as date) AS `queue_date`,`sms_queue`.`status` AS `status`,`sms_queue`.`priority` AS `priority`,count(0) AS `message_count`,sum((case when (`sms_queue`.`status` = 'sent') then 1 else 0 end)) AS `sent_count`,sum((case when (`sms_queue`.`status` = 'failed') then 1 else 0 end)) AS `failed_count`,sum((case when (`sms_queue`.`status` = 'pending') then 1 else 0 end)) AS `pending_count` from `sms_queue` group by cast(`sms_queue`.`created_at` as date),`sms_queue`.`status`,`sms_queue`.`priority` order by `queue_date` desc,`sms_queue`.`priority` desc;

-- --------------------------------------------------------
DROP VIEW IF EXISTS `vw_unmatched_payments`;
CREATE OR REPLACE VIEW `vw_unmatched_payments` AS select `p`.`id` AS `id`,`p`.`mpesa_receipt_number` AS `mpesa_receipt_number`,`p`.`amount` AS `amount`,`p`.`transaction_date` AS `transaction_date`,`p`.`sender_phone` AS `sender_phone`,`p`.`sender_name` AS `sender_name`,`p`.`paybill_account` AS `paybill_account`,`p`.`reconciliation_status` AS `reconciliation_status`,`p`.`created_at` AS `created_at`,`m`.`id` AS `member_id`,`m`.`member_number` AS `member_number`,`u`.`first_name` AS `first_name`,`u`.`last_name` AS `last_name`,`m`.`id_number` AS `id_number`,`u`.`phone` AS `phone_number` from ((`payments` `p` left join `members` `m` on((`p`.`member_id` = `m`.`id`))) left join `users` `u` on((`m`.`user_id` = `u`.`id`))) where (`p`.`reconciliation_status` = 'unmatched') order by `p`.`transaction_date` desc;

-- --------------------------------------------------------
DROP VIEW IF EXISTS `vw_upgrade_statistics`;
CREATE OR REPLACE VIEW `vw_upgrade_statistics` AS select count(0) AS `total_upgrades`,sum((case when (`plan_upgrade_requests`.`status` = 'completed') then 1 else 0 end)) AS `completed_upgrades`,sum((case when (`plan_upgrade_requests`.`status` = 'pending') then 1 else 0 end)) AS `pending_upgrades`,sum((case when (`plan_upgrade_requests`.`status` = 'failed') then 1 else 0 end)) AS `failed_upgrades`,sum((case when (`plan_upgrade_requests`.`status` = 'completed') then `plan_upgrade_requests`.`prorated_amount` else 0 end)) AS `total_upgrade_revenue`,avg((case when (`plan_upgrade_requests`.`status` = 'completed') then `plan_upgrade_requests`.`prorated_amount` end)) AS `avg_upgrade_amount`,avg((case when (`plan_upgrade_requests`.`status` = 'completed') then (to_days(`plan_upgrade_requests`.`completed_at`) - to_days(`plan_upgrade_requests`.`requested_at`)) end)) AS `avg_processing_days` from `plan_upgrade_requests` where (`plan_upgrade_requests`.`requested_at` >= (now() - interval 12 month));


-- ============================================================
-- POST-SCHEMA EXTENSIONS (added after Feb-23-2026 snapshot)
-- ============================================================

-- ── support_tickets ──────────────────────────────────────────
DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE `support_tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int NOT NULL,
  `user_id` int NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `status` enum('open','in_progress','resolved','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `response` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responded_by` int DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_support_member` (`member_id`),
  KEY `idx_support_status` (`status`),
  KEY `idx_support_priority` (`priority`),
  KEY `idx_support_created` (`created_at`),
  CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_ibfk_3` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── mpesa_stk_push_logs ──────────────────────────────────────
DROP TABLE IF EXISTS `mpesa_stk_push_logs`;
CREATE TABLE `mpesa_stk_push_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payment_id` int DEFAULT NULL,
  `merchant_request_id` varchar(100) NOT NULL,
  `checkout_request_id` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `account_reference` varchar(100) DEFAULT NULL,
  `transaction_desc` varchar(255) DEFAULT NULL,
  `result_code` varchar(10) DEFAULT NULL,
  `result_desc` text DEFAULT NULL,
  `mpesa_receipt_number` varchar(50) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `request_sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `callback_received_at` datetime DEFAULT NULL,
  `callback_data` text DEFAULT NULL COMMENT 'Full callback JSON',
  `status` enum('pending','success','failed','timeout') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stk_checkout_request` (`checkout_request_id`),
  KEY `idx_stk_merchant_request` (`merchant_request_id`),
  KEY `idx_stk_phone_number` (`phone_number`),
  KEY `idx_stk_status` (`status`),
  KEY `idx_stk_request_sent_at` (`request_sent_at`),
  CONSTRAINT `mpesa_stk_push_logs_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── mpesa_configuration ──────────────────────────────────────
DROP TABLE IF EXISTS `mpesa_configuration`;
CREATE TABLE `mpesa_configuration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `environment` enum('sandbox','production') DEFAULT 'sandbox',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_key_env` (`config_key`,`environment`),
  KEY `idx_mpesa_config_key` (`config_key`),
  KEY `idx_mpesa_environment` (`environment`),
  KEY `idx_mpesa_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── mpesa_config_audit ───────────────────────────────────────
DROP TABLE IF EXISTS `mpesa_config_audit`;
CREATE TABLE `mpesa_config_audit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `config_id` int NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text NOT NULL,
  `changed_by` int DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mpesa_audit_config_id` (`config_id`),
  KEY `idx_mpesa_audit_changed_at` (`changed_at`),
  CONSTRAINT `mpesa_config_audit_ibfk_1` FOREIGN KEY (`config_id`) REFERENCES `mpesa_configuration` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mpesa_config_audit_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── STK Push Views ───────────────────────────────────────────
DROP VIEW IF EXISTS `vw_pending_stk_pushes`;
CREATE OR REPLACE VIEW `vw_pending_stk_pushes` AS
SELECT
  l.id,
  l.checkout_request_id,
  l.phone_number,
  l.amount,
  l.account_reference,
  l.status,
  l.request_sent_at,
  TIMESTAMPDIFF(MINUTE, l.request_sent_at, NOW()) AS minutes_pending,
  p.id AS payment_id,
  p.member_id,
  p.payment_type,
  m.member_number,
  CONCAT(u.first_name, ' ', u.last_name) AS member_name
FROM mpesa_stk_push_logs l
LEFT JOIN payments p ON l.payment_id = p.id
LEFT JOIN members m ON p.member_id = m.id
LEFT JOIN users u ON m.user_id = u.id
WHERE l.status = 'pending'
  AND TIMESTAMPDIFF(MINUTE, l.request_sent_at, NOW()) < 5
ORDER BY l.request_sent_at DESC;

DROP VIEW IF EXISTS `vw_failed_stk_pushes`;
CREATE OR REPLACE VIEW `vw_failed_stk_pushes` AS
SELECT
  l.id,
  l.checkout_request_id,
  l.phone_number,
  l.amount,
  l.result_code,
  l.result_desc,
  l.request_sent_at,
  l.callback_received_at,
  p.id AS payment_id,
  p.member_id,
  m.member_number,
  CONCAT(u.first_name, ' ', u.last_name) AS member_name
FROM mpesa_stk_push_logs l
LEFT JOIN payments p ON l.payment_id = p.id
LEFT JOIN members m ON p.member_id = m.id
LEFT JOIN users u ON m.user_id = u.id
WHERE l.status = 'failed'
ORDER BY l.request_sent_at DESC;

-- ── Stored procedure: timeout old pending STK pushes ─────────
DROP PROCEDURE IF EXISTS `timeout_old_stk_pushes`;
DELIMITER //
CREATE PROCEDURE `timeout_old_stk_pushes`()
BEGIN
  UPDATE mpesa_stk_push_logs
  SET status = 'timeout',
      result_desc = 'Request timed out – no callback within 5 minutes',
      updated_at = NOW()
  WHERE status = 'pending'
    AND TIMESTAMPDIFF(MINUTE, request_sent_at, NOW()) >= 5;

  UPDATE payments p
  INNER JOIN mpesa_stk_push_logs l ON p.transaction_reference = l.checkout_request_id
  SET p.status = 'failed',
      p.notes = CONCAT(COALESCE(p.notes, ''), ' [STK Timeout]')
  WHERE l.status = 'timeout'
    AND p.status = 'pending';
END //
DELIMITER ;

-- ── Seed: initial sms_credits row ────────────────────────────
INSERT IGNORE INTO `sms_credits` (`id`, `balance`, `low_balance_threshold`)
VALUES (1, 0, 100);

-- ── Seed: mpesa_configuration defaults (update values before going live) ──────
INSERT INTO `mpesa_configuration` (`config_key`, `config_value`, `environment`, `description`) VALUES
('business_shortcode', '174379',                                       'sandbox',    'Sandbox Business Shortcode'),
('business_shortcode', '4163987',                                      'production', 'Production Business Shortcode'),
('passkey',            'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919', 'sandbox', 'Sandbox STK Passkey'),
('passkey',            'REPLACE_WITH_PROD_PASSKEY_FROM_SAFARICOM',     'production', 'Production STK Passkey'),
('api_url',            'https://sandbox.safaricom.co.ke',              'sandbox',    'Sandbox API Base URL'),
('api_url',            'https://api.safaricom.co.ke',                  'production', 'Production API Base URL'),
('callback_url',       'https://shenacompanion.co.ke/public/mpesa-stk-callback.php', 'production', 'STK Callback URL'),
('c2b_callback_url',   'https://shenacompanion.co.ke/public/mpesa-c2b-callback.php', 'production', 'C2B Callback URL')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
