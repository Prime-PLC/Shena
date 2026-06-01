ALTER TABLE members
    ADD COLUMN archived_at DATETIME NULL AFTER updated_at,
    ADD COLUMN archived_by INT NULL AFTER archived_at,
    ADD COLUMN archive_reason TEXT NULL AFTER archived_by,
    ADD INDEX idx_members_archived_at (archived_at);
