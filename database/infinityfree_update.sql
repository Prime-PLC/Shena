-- =============================================================================
-- Shena Companion Welfare Association - InfinityFree Database Update Script
-- Generated: 2026-03-15
-- Run this entire script in phpMyAdmin on your InfinityFree database.
-- Sections 1-3 (agents, payout_requests, resources) are ALREADY applied.
-- This script only applies the changes that are still missing.
-- Safe to re-run: IF NOT EXISTS / ON DUPLICATE KEY guards are used throughout.
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- SECTION 1: Link communications to payout_requests (may be missing)
-- -----------------------------------------------------------------------------

ALTER TABLE communications ADD COLUMN IF NOT EXISTS payout_request_id INT NULL;

-- Add the FK only if not already present
-- (If you get "Duplicate key" error on this line, skip it - it's already there)
ALTER TABLE communications ADD CONSTRAINT fk_communications_payout_request
    FOREIGN KEY (payout_request_id) REFERENCES payout_requests(id) ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- SECTION 2: Beneficiary date of birth
-- Migration: 010_add_beneficiary_dob.sql
-- -----------------------------------------------------------------------------

ALTER TABLE beneficiaries ADD COLUMN IF NOT EXISTS date_of_birth DATE NULL AFTER relationship;

-- -----------------------------------------------------------------------------
-- SECTION 3: Optional email (make users.email nullable)
-- Migration: 011_allow_null_email.sql
-- -----------------------------------------------------------------------------

ALTER TABLE users
    MODIFY COLUMN email VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL;

-- -----------------------------------------------------------------------------
-- SECTION 4: Membership tiers standardisation  ← March 15 2026  (REQUIRED)
-- Migration: 20260315_standardize_membership_tiers.sql
-- -----------------------------------------------------------------------------

-- Expand package ENUM on members table
ALTER TABLE members
    MODIFY COLUMN package ENUM(
        'individual',
        'family',
        'extended_family_1',
        'extended_family_2',
        'executive',
        'couple',
        'basic',
        'premium'
    ) COLLATE utf8mb4_unicode_ci DEFAULT 'individual';

-- Add corporate_couple_count column
ALTER TABLE members
    ADD COLUMN IF NOT EXISTS corporate_couple_count TINYINT UNSIGNED NOT NULL DEFAULT 0
    AFTER monthly_contribution;

-- Expand ENUMs on plan_upgrade_requests
ALTER TABLE plan_upgrade_requests
    MODIFY COLUMN from_package ENUM(
        'individual',
        'family',
        'extended_family_1',
        'extended_family_2',
        'executive',
        'couple'
    ) COLLATE utf8mb4_unicode_ci NOT NULL,
    MODIFY COLUMN to_package ENUM(
        'individual',
        'family',
        'extended_family_1',
        'extended_family_2',
        'executive',
        'couple'
    ) COLLATE utf8mb4_unicode_ci NOT NULL;

-- Expand ENUMs on plan_upgrade_history
ALTER TABLE plan_upgrade_history
    MODIFY COLUMN from_package ENUM(
        'individual',
        'family',
        'extended_family_1',
        'extended_family_2',
        'executive',
        'couple'
    ) COLLATE utf8mb4_unicode_ci NOT NULL,
    MODIFY COLUMN to_package ENUM(
        'individual',
        'family',
        'extended_family_1',
        'extended_family_2',
        'executive',
        'couple'
    ) COLLATE utf8mb4_unicode_ci NOT NULL;

-- Normalise any legacy 'couple' rows to 'family'
UPDATE members SET package = 'family' WHERE package = 'couple';

-- -----------------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 1;
-- Done.
-- =============================================================================

