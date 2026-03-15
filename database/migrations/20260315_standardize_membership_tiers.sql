-- Standardize membership tiers and pricing support
-- Date: 2026-03-15

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
    ) DEFAULT 'individual',
    ADD COLUMN corporate_couple_count TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER monthly_contribution;

ALTER TABLE plan_upgrade_requests
    MODIFY COLUMN from_package ENUM(
        'individual',
        'family',
        'extended_family_1',
        'extended_family_2',
        'executive',
        'couple'
    ) NOT NULL,
    MODIFY COLUMN to_package ENUM(
        'individual',
        'family',
        'extended_family_1',
        'extended_family_2',
        'executive',
        'couple'
    ) NOT NULL;

ALTER TABLE plan_upgrade_history
    MODIFY COLUMN from_package ENUM(
        'individual',
        'family',
        'extended_family_1',
        'extended_family_2',
        'executive',
        'couple'
    ) NOT NULL,
    MODIFY COLUMN to_package ENUM(
        'individual',
        'family',
        'extended_family_1',
        'extended_family_2',
        'executive',
        'couple'
    ) NOT NULL;

-- Optional legacy normalization for existing rows.
UPDATE members
SET package = 'family'
WHERE package = 'couple';
