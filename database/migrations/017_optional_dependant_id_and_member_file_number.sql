ALTER TABLE beneficiaries
    MODIFY id_number VARCHAR(20) NULL;

ALTER TABLE members
    ADD COLUMN file_number VARCHAR(50) NULL AFTER id_number,
    ADD INDEX idx_members_file_number (file_number);
