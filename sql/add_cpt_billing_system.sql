-- ============================================
-- 1. CREATE CPT CODES TABLE (NO MODIFIER COLUMN)
-- ============================================
CREATE TABLE IF NOT EXISTS cpt_codes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(10) NOT NULL UNIQUE,
  category VARCHAR(100),
  type VARCHAR(20) DEFAULT 'CPT4',
  description TEXT,
  standard_duration_minutes INT DEFAULT 50,
  standard_fee DECIMAL(10,2) NULL COMMENT 'Standard insurance billing rate',
  is_active BOOLEAN DEFAULT 1,
  is_addon BOOLEAN DEFAULT 0,
  requires_primary_code VARCHAR(10) NULL COMMENT 'For add-on codes, which primary CPT required',
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_category (category),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. SEED CPT CODES (NO MODIFIERS) - SKIP IF EXISTS
-- ============================================
INSERT IGNORE INTO cpt_codes (code, category, description, standard_duration_minutes, standard_fee, sort_order) VALUES
('00000', 'Non-Billable', 'Non-Billable', 50, NULL, 0),
('90791', 'Intake', 'Intake Interview', 60, 150.00, 10),
('90832', 'Individual Therapy', 'Psychotherapy 16–37 min', 30, 100.00, 20),
('90834', 'Individual Therapy', 'Psychotherapy 45–50 min', 50, 150.00, 30),
('90837', 'Individual Therapy', 'Psychotherapy 54+ min', 60, 180.00, 40),
('90839', 'Individual Therapy', 'Psychotherapy – Crisis', 60, 200.00, 50),
('90846', 'Family Therapy', 'Family Therapy (w/o patient)', 50, 150.00, 60),
('90847', 'Family Therapy', 'Family Therapy (w/ patient)', 50, 150.00, 70),
('90853', 'Group Therapy', 'Group Therapy', 60, 75.00, 80);

-- ============================================
-- 3. CREATE BILLING MODIFIERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS billing_modifiers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(10) NOT NULL UNIQUE,
  description VARCHAR(255) NOT NULL,
  modifier_type ENUM('telehealth', 'clinician', 'administrative', 'mh-specific') NOT NULL,
  is_active BOOLEAN DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_type (modifier_type),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. SEED BILLING MODIFIERS - SKIP IF EXISTS
-- ============================================
INSERT IGNORE INTO billing_modifiers (code, description, modifier_type, sort_order) VALUES
-- Telehealth Modifiers
('95', 'Telehealth (Synchronous)', 'telehealth', 10),
('93', 'Telehealth (Audio Only)', 'telehealth', 20),
('GT', 'Telehealth (Legacy)', 'telehealth', 30),

-- Clinician Type Modifiers
('AH', 'Clinical Psychologist (PhD/PsyD)', 'clinician', 40),
('AJ', 'Clinical Social Worker', 'clinician', 50),
('HO', 'Master\'s Level Therapist', 'clinician', 60),
('HN', 'Licensed Clinical Mental Health Counselor', 'clinician', 70),

-- Administrative Modifiers
('59', 'Distinct Procedural Service', 'administrative', 80),
('25', 'Significant, Separately Identifiable E/M Service', 'administrative', 90),
('76', 'Repeat Procedure by Same Physician', 'administrative', 100),
('77', 'Repeat Procedure by Another Physician', 'administrative', 110),
('KX', 'Requirements Specified in Medical Policy Met', 'administrative', 120),
('GA', 'Waiver of Liability (Issued as Required by Payer)', 'administrative', 130),
('GY', 'Item/Service Statutorily Excluded', 'administrative', 140),
('GZ', 'Item/Service Expected to be Denied', 'administrative', 150),

-- MH-Specific Modifiers
('HA', 'Child/Adolescent Program', 'mh-specific', 160),
('HQ', 'Group Setting', 'mh-specific', 170),
('TF', 'Intermediate Level of Care', 'mh-specific', 180),
('TG', 'Complex/High-Tech Level of Care', 'mh-specific', 190),
('U1', 'Medicaid Level of Care 1', 'mh-specific', 200),
('U2', 'Medicaid Level of Care 2', 'mh-specific', 210),
('U3', 'Medicaid Level of Care 3', 'mh-specific', 220),
('U4', 'Medicaid Level of Care 4', 'mh-specific', 230),
('U5', 'Medicaid Level of Care 5', 'mh-specific', 240),
('U6', 'Medicaid Level of Care 6', 'mh-specific', 250),
('U7', 'Medicaid Level of Care 7', 'mh-specific', 260),
('U8', 'Medicaid Level of Care 8', 'mh-specific', 270),
('U9', 'Medicaid Level of Care 9', 'mh-specific', 280);

-- ============================================
-- 5. CREATE CATEGORY <-> CPT JUNCTION TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS category_cpt_codes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  category_id BIGINT UNSIGNED NOT NULL,
  cpt_code_id INT NOT NULL,
  is_default BOOLEAN DEFAULT 0 COMMENT 'Default CPT for this category',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES appointment_categories(id) ON DELETE CASCADE,
  FOREIGN KEY (cpt_code_id) REFERENCES cpt_codes(id) ON DELETE CASCADE,
  UNIQUE KEY unique_category_cpt (category_id, cpt_code_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. ENHANCE APPOINTMENT_CATEGORIES TABLE
-- ============================================
ALTER TABLE appointment_categories
  ADD COLUMN IF NOT EXISTS category_type ENUM('client', 'clinic', 'holiday') DEFAULT 'client' COMMENT 'Client=billable encounter, Clinic=internal time, Holiday=closure',
  ADD COLUMN IF NOT EXISTS requires_cpt_selection BOOLEAN DEFAULT 0 COMMENT 'Show CPT dropdown when scheduling',
  ADD COLUMN IF NOT EXISTS blocks_availability BOOLEAN DEFAULT 0 COMMENT 'Blocks provider availability';

-- ============================================
-- 7. ENHANCE USERS FOR DEFAULT MODIFIER
-- ============================================
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS default_modifier_id INT NULL COMMENT 'Default billing modifier based on credentials',
  ADD INDEX IF NOT EXISTS idx_default_modifier (default_modifier_id);

-- Optional: Add foreign key if you want referential integrity
-- ALTER TABLE users ADD CONSTRAINT fk_users_modifier FOREIGN KEY (default_modifier_id) REFERENCES billing_modifiers(id) ON DELETE SET NULL;

-- ============================================
-- 8. ENHANCE CLIENTS FOR PAYMENT TRACKING
-- ============================================
ALTER TABLE clients
  ADD COLUMN IF NOT EXISTS payment_type ENUM('insurance', 'self-pay', 'pro-bono') DEFAULT 'insurance' COMMENT 'How client pays for services',
  ADD COLUMN IF NOT EXISTS custom_session_fee DECIMAL(10,2) NULL COMMENT 'Negotiated rate for self-pay/pro-bono clients';

-- ============================================
-- 9. ENHANCE APPOINTMENTS FOR CPT & BILLING
-- ============================================
ALTER TABLE appointments
  ADD COLUMN IF NOT EXISTS cpt_code_id INT NULL COMMENT 'CPT code used for this appointment',
  ADD COLUMN IF NOT EXISTS modifier_id INT NULL COMMENT 'Billing modifier (override provider default)',
  ADD COLUMN IF NOT EXISTS billing_fee DECIMAL(10,2) NULL COMMENT 'Actual fee charged for this appointment',
  ADD COLUMN IF NOT EXISTS fee_type ENUM('cpt', 'custom', 'pro-bono', 'none') DEFAULT 'none' COMMENT 'How fee was determined',
  ADD INDEX IF NOT EXISTS idx_cpt_code (cpt_code_id),
  ADD INDEX IF NOT EXISTS idx_modifier (modifier_id);

-- Optional: Add foreign keys for referential integrity
-- ALTER TABLE appointments ADD CONSTRAINT fk_appt_cpt FOREIGN KEY (cpt_code_id) REFERENCES cpt_codes(id) ON DELETE SET NULL;
-- ALTER TABLE appointments ADD CONSTRAINT fk_appt_modifier FOREIGN KEY (modifier_id) REFERENCES billing_modifiers(id) ON DELETE SET NULL;

