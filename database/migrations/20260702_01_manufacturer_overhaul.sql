-- 20260702_01_manufacturer_overhaul.sql
-- Foundation migration for manufacturer onboarding, generated codes, and automated alert routing.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS manufacturer_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    license_number VARCHAR(120) NOT NULL,
    country VARCHAR(120) DEFAULT NULL,
    contact_phone VARCHAR(60) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    approval_status VARCHAR(40) NOT NULL DEFAULT 'Pending',
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME DEFAULT NULL,
    reviewed_by INT DEFAULT NULL,
    review_notes TEXT DEFAULT NULL,
    CONSTRAINT uq_manufacturer_license UNIQUE (license_number),
    CONSTRAINT uq_manufacturer_user UNIQUE (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS manufacturer_approval_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    old_status VARCHAR(40) DEFAULT NULL,
    new_status VARCHAR(40) NOT NULL,
    action_notes TEXT DEFAULT NULL,
    acted_by INT DEFAULT NULL,
    acted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_approval_profile (profile_id),
    INDEX idx_approval_actor (acted_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS medicine_batches (
    batch_id INT AUTO_INCREMENT PRIMARY KEY,
    manufacturer_user_id INT NOT NULL,
    med_name VARCHAR(255) NOT NULL,
    batch_code VARCHAR(80) NOT NULL,
    manufacture_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    planned_pack_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_medicine_batch_code UNIQUE (batch_code),
    INDEX idx_batch_manufacturer (manufacturer_user_id),
    INDEX idx_batch_name (med_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS medicine_pack_codes (
    pack_id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    pack_code VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_pack_code UNIQUE (pack_code),
    INDEX idx_pack_batch (batch_id),
    INDEX idx_pack_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE users
    MODIFY role VARCHAR(80) NOT NULL;

ALTER TABLE verification_log
    ADD COLUMN verification_type VARCHAR(30) NOT NULL DEFAULT 'Batch' AFTER batchNumber,
    ADD COLUMN actor_role VARCHAR(50) DEFAULT NULL AFTER userID;

ALTER TABLE report
    ADD COLUMN source_type VARCHAR(30) NOT NULL DEFAULT 'Manual' AFTER description,
    ADD COLUMN verification_log_id INT DEFAULT NULL AFTER userID;

COMMIT;
