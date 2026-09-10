-- Required for optional member-registration email. Existing addresses and the
-- unique email index remain intact; multiple members may omit email (NULL).
ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NULL DEFAULT NULL;
