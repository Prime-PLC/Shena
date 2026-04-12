-- Test users seed for Shena Companion
-- Safe to run multiple times (removes prior test users by email first)
-- Run in phpMyAdmin after importing schema.sql

START TRANSACTION;

-- 1) Cleanup previous test users (cascades to members/agents via FK)
DELETE FROM users
WHERE email IN (
  'test.superadmin@shena.local',
  'test.agent@shena.local',
  'test.member@shena.local'
);

-- 2) Super Admin user
-- Password: Admin@1234
INSERT INTO users (
  first_name,
  last_name,
  email,
  phone,
  password,
  role,
  status,
  email_verified_at,
  last_login
) VALUES (
  'Super',
  'Admin',
  'test.superadmin@shena.local',
  '254700000101',
  '$2y$12$K1UaOZHrAkI9.cvLO9lWk.wIu8nXsuVnbXKz/uc17oTOIuXunSNeW',
  'super_admin',
  'active',
  NOW(),
  NULL
);
SET @super_admin_user_id := LAST_INSERT_ID();

-- 3) Agent user + agent profile
-- Password: Agent@1234
INSERT INTO users (
  first_name,
  last_name,
  email,
  phone,
  password,
  role,
  status,
  email_verified_at,
  last_login
) VALUES (
  'Test',
  'Agent',
  'test.agent@shena.local',
  '254700000102',
  '$2y$12$iTYCspQiS2mPhSFSGO0XCO8LmKxy//KhpfIAT409JD43T0W8cyk4W',
  'agent',
  'active',
  NOW(),
  NULL
);
SET @agent_user_id := LAST_INSERT_ID();

INSERT INTO agents (
  user_id,
  agent_number,
  first_name,
  last_name,
  national_id,
  phone,
  email,
  address,
  county,
  status,
  commission_rate,
  total_members,
  total_commission,
  registration_date,
  activated_at
) VALUES (
  @agent_user_id,
  'AGT-TEST-001',
  'Test',
  'Agent',
  '90000001',
  '254700000102',
  'test.agent@shena.local',
  'Nairobi, Kenya',
  'Nairobi',
  'active',
  10.00,
  0,
  0.00,
  CURDATE(),
  NOW()
);
SET @agent_id := LAST_INSERT_ID();

-- 4) Member user + member profile (linked to test agent)
-- Password: Member@1234
INSERT INTO users (
  first_name,
  last_name,
  email,
  phone,
  password,
  role,
  status,
  email_verified_at,
  last_login
) VALUES (
  'Test',
  'Member',
  'test.member@shena.local',
  '254700000103',
  '$2y$12$76HsQi54NyEpAEGQnaj.u.DL2yArYn86gjMa5F5eymPL4mdVI76HC',
  'member',
  'active',
  NOW(),
  NULL
);
SET @member_user_id := LAST_INSERT_ID();

INSERT INTO members (
  user_id,
  agent_id,
  member_number,
  id_number,
  date_of_birth,
  gender,
  address,
  next_of_kin,
  next_of_kin_relationship,
  next_of_kin_phone,
  package,
  package_key,
  monthly_contribution,
  status,
  maturity_ends,
  coverage_ends,
  payment_deadline
) VALUES (
  @member_user_id,
  @agent_id,
  'MEM-TEST-001',
  '80000001',
  '1992-01-15',
  'male',
  'Nairobi, Kenya',
  'Jane Member',
  'Spouse',
  '254700000104',
  'individual',
  'individual_18_80',
  500.00,
  'active',
  DATE_SUB(CURDATE(), INTERVAL 1 DAY),
  DATE_ADD(CURDATE(), INTERVAL 1 YEAR),
  DATE_ADD(CURDATE(), INTERVAL 30 DAY)
);
SET @member_id := LAST_INSERT_ID();

COMMIT;

-- Quick verification output
SELECT id, first_name, last_name, email, role, status
FROM users
WHERE id IN (@super_admin_user_id, @agent_user_id, @member_user_id)
ORDER BY FIELD(role, 'super_admin', 'agent', 'member');

SELECT id, user_id, agent_number, status
FROM agents
WHERE id = @agent_id;

SELECT id, user_id, agent_id, member_number, status, package
FROM members
WHERE id = @member_id;
