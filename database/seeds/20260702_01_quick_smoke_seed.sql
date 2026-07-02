-- 20260702_01_quick_smoke_seed.sql
-- Quick seed data for role-based smoke testing.
-- Safe to run multiple times: uses deterministic emails/codes and upserts where possible.

START TRANSACTION;

-- 1) Ensure role users exist (passwords are plain text on purpose for quick local smoke tests).
INSERT INTO users (CustomerName, email, passwordHash, role, status, phoneNumber, firstRegistration)
SELECT 'Patient Demo', 'patient.demo@med.local', 'Test1234', 'Customer', 1, '0700100001', CURDATE()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'patient.demo@med.local');

INSERT INTO users (CustomerName, email, passwordHash, role, status, phoneNumber, firstRegistration)
SELECT 'Pharmacist Demo', 'pharmacist.demo@med.local', 'Test1234', 'Pharmacist', 1, '0700100002', CURDATE()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'pharmacist.demo@med.local');

INSERT INTO users (CustomerName, email, passwordHash, role, status, phoneNumber, firstRegistration)
SELECT 'Regulator Demo', 'regulator.demo@med.local', 'Test1234', 'Regulator', 1, '0700100003', CURDATE()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'regulator.demo@med.local');

INSERT INTO users (CustomerName, email, passwordHash, role, status, phoneNumber, firstRegistration)
SELECT 'Manufacturer Approved Demo', 'manufacturer.approved@med.local', 'Test1234', 'Manufacturer', 1, '0700100004', CURDATE()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'manufacturer.approved@med.local');

INSERT INTO users (CustomerName, email, passwordHash, role, status, phoneNumber, firstRegistration)
SELECT 'Manufacturer Pending Demo', 'manufacturer.pending@med.local', 'Test1234', 'Manufacturer', 0, '0700100005', CURDATE()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'manufacturer.pending@med.local');

-- 2) Ensure manufacturer profiles exist for both approved and pending accounts.
INSERT INTO manufacturer_profiles
    (user_id, company_name, license_number, country, contact_phone, address, approval_status, submitted_at, reviewed_at, reviewed_by, review_notes)
SELECT u.customerID, 'Acme Pharma Demo', 'LIC-APPROVED-001', 'Kenya', '0700100004', 'Nairobi Demo Park', 'Approved', NOW(), NOW(), NULL, 'Seed approved account for smoke test.'
FROM users u
WHERE u.email = 'manufacturer.approved@med.local'
  AND NOT EXISTS (
      SELECT 1 FROM manufacturer_profiles mp WHERE mp.user_id = u.customerID
  );

INSERT INTO manufacturer_profiles
    (user_id, company_name, license_number, country, contact_phone, address, approval_status, submitted_at, reviewed_at, reviewed_by, review_notes)
SELECT u.customerID, 'Beta Labs Demo', 'LIC-PENDING-001', 'Kenya', '0700100005', 'Mombasa Demo Yard', 'Pending', NOW(), NULL, NULL, NULL
FROM users u
WHERE u.email = 'manufacturer.pending@med.local'
  AND NOT EXISTS (
      SELECT 1 FROM manufacturer_profiles mp WHERE mp.user_id = u.customerID
  );

-- 3) Ensure deterministic batches and packs exist (for pharmacist/patient verification tests).
INSERT INTO medicine_batches
    (manufacturer_user_id, med_name, batch_code, manufacture_date, expiry_date, planned_pack_count, created_at)
SELECT u.customerID, 'Paracetamol 500mg Demo', 'BTHDEMO000001', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 365 DAY), 2, NOW()
FROM users u
WHERE u.email = 'manufacturer.approved@med.local'
  AND NOT EXISTS (
      SELECT 1 FROM medicine_batches b WHERE b.batch_code = 'BTHDEMO000001'
  );

INSERT INTO medicine_batches
    (manufacturer_user_id, med_name, batch_code, manufacture_date, expiry_date, planned_pack_count, created_at)
SELECT u.customerID, 'Amoxicillin 250mg Demo', 'BTHDEMO000002', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 300 DAY), 1, NOW()
FROM users u
WHERE u.email = 'manufacturer.approved@med.local'
  AND NOT EXISTS (
      SELECT 1 FROM medicine_batches b WHERE b.batch_code = 'BTHDEMO000002'
  );

-- Keep legacy medicine table aligned for modules still using fallback joins.
INSERT INTO medicine (medName, manufacture, batchNumber, manufactureDate, expiryDate)
SELECT 'Paracetamol 500mg Demo', 'Acme Pharma Demo', 'BTHDEMO000001', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 365 DAY)
WHERE NOT EXISTS (SELECT 1 FROM medicine WHERE batchNumber = 'BTHDEMO000001');

INSERT INTO medicine (medName, manufacture, batchNumber, manufactureDate, expiryDate)
SELECT 'Amoxicillin 250mg Demo', 'Acme Pharma Demo', 'BTHDEMO000002', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 300 DAY)
WHERE NOT EXISTS (SELECT 1 FROM medicine WHERE batchNumber = 'BTHDEMO000002');

-- Insert deterministic pack codes tied to seeded batches.
INSERT INTO medicine_pack_codes (batch_id, pack_code, is_active, created_at)
SELECT b.batch_id, 'PKDEMO0000000001', 1, NOW()
FROM medicine_batches b
WHERE b.batch_code = 'BTHDEMO000001'
  AND NOT EXISTS (SELECT 1 FROM medicine_pack_codes p WHERE p.pack_code = 'PKDEMO0000000001');

INSERT INTO medicine_pack_codes (batch_id, pack_code, is_active, created_at)
SELECT b.batch_id, 'PKDEMO0000000002', 1, NOW()
FROM medicine_batches b
WHERE b.batch_code = 'BTHDEMO000001'
  AND NOT EXISTS (SELECT 1 FROM medicine_pack_codes p WHERE p.pack_code = 'PKDEMO0000000002');

INSERT INTO medicine_pack_codes (batch_id, pack_code, is_active, created_at)
SELECT b.batch_id, 'PKDEMO0000000003', 1, NOW()
FROM medicine_batches b
WHERE b.batch_code = 'BTHDEMO000002'
  AND NOT EXISTS (SELECT 1 FROM medicine_pack_codes p WHERE p.pack_code = 'PKDEMO0000000003');

-- 4) Seed verification logs for analytics pages.
INSERT INTO verification_log (userID, actor_role, batchNumber, verification_type, result, verified_at)
SELECT u.customerID, 'Pharmacist', 'BTHDEMO000001', 'Batch', 'Genuine', NOW()
FROM users u
WHERE u.email = 'pharmacist.demo@med.local'
  AND NOT EXISTS (
      SELECT 1 FROM verification_log v
      WHERE v.userID = u.customerID
        AND v.batchNumber = 'BTHDEMO000001'
        AND COALESCE(v.verification_type, 'Batch') = 'Batch'
        AND v.result = 'Genuine'
  );

INSERT INTO verification_log (userID, actor_role, batchNumber, verification_type, result, verified_at)
SELECT u.customerID, 'Patient', 'PKDEMO0000000001', 'Pack', 'Genuine', NOW()
FROM users u
WHERE u.email = 'patient.demo@med.local'
  AND NOT EXISTS (
      SELECT 1 FROM verification_log v
      WHERE v.userID = u.customerID
        AND v.batchNumber = 'PKDEMO0000000001'
        AND COALESCE(v.verification_type, 'Batch') = 'Pack'
        AND v.result = 'Genuine'
  );

INSERT INTO verification_log (userID, actor_role, batchNumber, verification_type, result, verified_at)
SELECT u.customerID, 'Patient', 'PKDEMOFAKE000001', 'Pack', 'Counterfeit', NOW()
FROM users u
WHERE u.email = 'patient.demo@med.local'
  AND NOT EXISTS (
      SELECT 1 FROM verification_log v
      WHERE v.userID = u.customerID
        AND v.batchNumber = 'PKDEMOFAKE000001'
        AND COALESCE(v.verification_type, 'Batch') = 'Pack'
        AND v.result = 'Counterfeit'
  );

-- 5) Seed reports (manual + auto alert style) for regulator feed.
INSERT INTO report (userID, verification_log_id, batchNumber, description, source_type, status, reported_at)
SELECT u.customerID,
       (
         SELECT v.loginID
         FROM verification_log v
         WHERE v.userID = u.customerID
           AND v.batchNumber = 'PKDEMOFAKE000001'
         ORDER BY v.verified_at DESC
         LIMIT 1
       ) AS verification_log_id,
       'PKDEMOFAKE000001',
       'Automatically generated alert after counterfeit patient verification.',
       'AutoAlert',
       'Pending',
       NOW()
FROM users u
WHERE u.email = 'patient.demo@med.local'
  AND NOT EXISTS (
      SELECT 1 FROM report r
      WHERE r.batchNumber = 'PKDEMOFAKE000001'
        AND COALESCE(r.source_type, 'Manual') = 'AutoAlert'
  );

INSERT INTO report (userID, batchNumber, description, source_type, status, reported_at)
SELECT u.customerID,
       'PKDEMO0000000003',
       'Blister packaging looked tampered and text print quality seemed poor on one pack.',
       'Manual',
       'Under Investigation',
       NOW()
FROM users u
WHERE u.email = 'patient.demo@med.local'
  AND NOT EXISTS (
      SELECT 1 FROM report r
      WHERE r.batchNumber = 'PKDEMO0000000003'
        AND COALESCE(r.source_type, 'Manual') = 'Manual'
  );

COMMIT;
