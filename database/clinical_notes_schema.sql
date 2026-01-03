-- ========================================
-- Mindline EMHR - Clinical Notes Tables
-- Phase 4: Clinical Documentation
-- ========================================
--
-- ARCHITECTURE PHILOSOPHY:
-- Notes are independent, primary objects.
-- Appointments, billing, and supervision REFERENCE notes.
-- This allows maximum flexibility for mental health workflows.
--
-- ========================================

-- ========================================
-- 1. CLINICAL NOTES (Primary Entity)
-- ========================================

CREATE TABLE IF NOT EXISTS clinical_notes (
    -- Primary identification
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_uuid VARCHAR(36) UNIQUE NOT NULL, -- For API references

    -- Core relationships (notes are standalone)
    patient_id INT NOT NULL,
    provider_id INT NOT NULL,

    -- Optional references (notes can exist without these)
    appointment_id INT NULL,
    billing_id INT NULL,

    -- Note metadata
    note_type VARCHAR(50) NOT NULL, -- 'progress', 'intake', 'crisis', 'discharge', 'admin', 'mse', 'treatment_plan'
    template_type VARCHAR(50) DEFAULT 'BIRP', -- 'BIRP', 'PIRP', 'SOAP', 'custom'
    service_date DATE NOT NULL,
    service_duration INT NULL, -- minutes (nullable for admin notes)
    service_location VARCHAR(100) NULL, -- 'office', 'telehealth', etc.

    -- BIRP/PIRP content (all nullable for flexibility)
    behavior_problem TEXT NULL, -- B (Behavior) or P (Problem)
    intervention TEXT NULL, -- I (Intervention)
    response TEXT NULL, -- R (Response)
    plan TEXT NULL, -- P (Plan)

    -- Additional clinical content
    risk_assessment TEXT NULL, -- Only when risk is flagged
    risk_present BOOLEAN DEFAULT FALSE,
    goals_addressed JSON NULL, -- Array of treatment goal IDs

    -- Quick-select data (stored as JSON arrays)
    interventions_selected JSON NULL, -- e.g., ["Psychoeducation", "Grounding techniques"]
    client_presentation JSON NULL, -- e.g., ["Engaged", "Tearful"]

    -- Diagnosis (pulled from treatment plan, but can be overridden)
    diagnosis_codes JSON NULL, -- Array of ICD-10 codes

    -- Free-form fields
    presenting_concerns TEXT NULL,
    clinical_observations TEXT NULL,
    mental_status_exam TEXT NULL, -- For MSE notes

    -- Status & workflow
    status VARCHAR(20) NOT NULL DEFAULT 'draft', -- 'draft', 'complete', 'signed', 'locked'
    is_locked BOOLEAN DEFAULT FALSE,

    -- Signatures & compliance
    signed_at TIMESTAMP NULL,
    signed_by INT NULL,
    signature_data TEXT NULL, -- Electronic signature details

    supervisor_review_required BOOLEAN DEFAULT FALSE,
    supervisor_review_status VARCHAR(20) NULL, -- 'pending', 'approved', 'returned'
    supervisor_signed_at TIMESTAMP NULL,
    supervisor_signed_by INT NULL,
    supervisor_comments TEXT NULL,

    -- Addendum support
    parent_note_id INT NULL, -- If this is an addendum
    is_addendum BOOLEAN DEFAULT FALSE,
    addendum_reason TEXT NULL,

    -- Audit trail
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    locked_at TIMESTAMP NULL,

    -- Auto-save tracking
    last_autosave_at TIMESTAMP NULL,

    -- Indexes for performance
    INDEX idx_patient (patient_id),
    INDEX idx_provider (provider_id),
    INDEX idx_appointment (appointment_id),
    INDEX idx_service_date (service_date),
    INDEX idx_note_type (note_type),
    INDEX idx_status (status),
    INDEX idx_supervisor_review (supervisor_review_status),
    INDEX idx_created_at (created_at),

    -- Foreign keys
    FOREIGN KEY (patient_id) REFERENCES patient_data(pid) ON DELETE RESTRICT,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (appointment_id) REFERENCES openemr_postcalendar_events(pc_eid) ON DELETE SET NULL,
    FOREIGN KEY (signed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (supervisor_signed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_note_id) REFERENCES clinical_notes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 2. NOTE DRAFTS (Auto-save Support)
-- ========================================

CREATE TABLE IF NOT EXISTS note_drafts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NULL, -- Nullable because draft may not be saved as note yet
    provider_id INT NOT NULL,
    patient_id INT NOT NULL,
    appointment_id INT NULL,

    -- Draft content (stored as JSON)
    draft_content JSON NOT NULL,

    -- Metadata
    note_type VARCHAR(50) NOT NULL,
    service_date DATE NOT NULL,

    -- Timestamps
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_note (note_id),
    INDEX idx_provider (provider_id),
    INDEX idx_patient (patient_id),
    INDEX idx_appointment (appointment_id),
    INDEX idx_saved_at (saved_at),

    -- Foreign keys
    FOREIGN KEY (note_id) REFERENCES clinical_notes(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patient_data(pid) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES openemr_postcalendar_events(pc_eid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 3. TREATMENT GOALS (For Carry-Forward)
-- ========================================

CREATE TABLE IF NOT EXISTS treatment_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    provider_id INT NOT NULL,

    -- Goal content
    goal_text TEXT NOT NULL,
    goal_category VARCHAR(50) NULL, -- 'symptom_reduction', 'skill_building', 'relationship', etc.

    -- Tracking
    target_date DATE NULL,
    status VARCHAR(20) DEFAULT 'active', -- 'active', 'achieved', 'revised', 'discontinued'
    progress_level INT NULL, -- 0-100 percentage

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    achieved_at TIMESTAMP NULL,
    discontinued_at TIMESTAMP NULL,

    -- Indexes
    INDEX idx_patient (patient_id),
    INDEX idx_provider (provider_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),

    -- Foreign keys
    FOREIGN KEY (patient_id) REFERENCES patient_data(pid) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 4. INTERVENTION LIBRARY (For Quick-Select)
-- ========================================

CREATE TABLE IF NOT EXISTS intervention_library (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Intervention details
    intervention_name VARCHAR(100) NOT NULL UNIQUE,
    intervention_tier INT NOT NULL, -- 1=Core, 2=Modality, 3=Crisis, 4=Admin
    modality VARCHAR(50) NULL, -- 'CBT', 'DBT', 'EMDR', etc. (for Tier 2)

    -- System vs custom
    is_system_intervention BOOLEAN DEFAULT TRUE,
    created_by INT NULL, -- Admin who created custom intervention

    -- Display
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_tier (intervention_tier),
    INDEX idx_modality (modality),
    INDEX idx_active (is_active),

    -- Foreign keys
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 5. USER FAVORITE INTERVENTIONS
-- ========================================

CREATE TABLE IF NOT EXISTS user_favorite_interventions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    intervention_id INT NOT NULL,
    display_order INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_user (user_id),
    UNIQUE KEY unique_user_intervention (user_id, intervention_id),

    -- Foreign keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (intervention_id) REFERENCES intervention_library(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 6. SYSTEM SETTINGS (For Admin Choices)
-- ========================================

CREATE TABLE IF NOT EXISTS clinical_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_type VARCHAR(20) DEFAULT 'string', -- 'string', 'boolean', 'json'

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT NULL,

    -- Foreign keys
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO clinical_settings (setting_key, setting_value, setting_type) VALUES
('default_note_template', 'BIRP', 'string'),
('require_supervisor_review', 'false', 'boolean'),
('auto_lock_notes_after_days', '7', 'string'),
('allow_post_signature_edits', 'true', 'boolean')
ON DUPLICATE KEY UPDATE setting_key=setting_key; -- No-op if exists

-- ========================================
-- 7. SEED INTERVENTION LIBRARY (Tier 1 - Core)
-- ========================================

INSERT INTO intervention_library (intervention_name, intervention_tier, modality, display_order) VALUES
-- Tier 1: Core interventions (always visible)
('Psychoeducation', 1, NULL, 1),
('Cognitive restructuring / reframing', 1, NULL, 2),
('Behavioral activation', 1, NULL, 3),
('Grounding techniques', 1, NULL, 4),
('Mindfulness / breathing exercises', 1, NULL, 5),
('Emotional regulation skills', 1, NULL, 6),
('Coping skills training', 1, NULL, 7),
('Safety planning', 1, NULL, 8),
('Supportive counseling', 1, NULL, 9),
('Validation / normalization', 1, NULL, 10),
('Motivational interviewing', 1, NULL, 11),
('Treatment plan review / goal alignment', 1, NULL, 12),

-- Tier 2: CBT
('Thought records', 2, 'CBT', 1),
('Cognitive distortions identification', 2, 'CBT', 2),
('Exposure planning', 2, 'CBT', 3),

-- Tier 2: DBT
('Distress tolerance skills', 2, 'DBT', 1),
('Interpersonal effectiveness skills', 2, 'DBT', 2),
('Chain analysis', 2, 'DBT', 3),

-- Tier 2: ACT
('Values clarification', 2, 'ACT', 1),
('Cognitive defusion', 2, 'ACT', 2),
('Acceptance strategies', 2, 'ACT', 3),

-- Tier 2: EMDR
('Resourcing / stabilization', 2, 'EMDR', 1),
('Bilateral stimulation', 2, 'EMDR', 2),
('Target identification', 2, 'EMDR', 3),

-- Tier 2: IFS
('Parts identification', 2, 'IFS', 1),
('Unblending', 2, 'IFS', 2),
('Self-energy access', 2, 'IFS', 3),

-- Tier 2: Solution-Focused
('Miracle question', 2, 'Solution-Focused', 1),
('Scaling questions', 2, 'Solution-Focused', 2),
('Exception finding', 2, 'Solution-Focused', 3),

-- Tier 3: Crisis/Risk (only when risk flagged)
('Suicide risk assessment', 3, NULL, 1),
('Crisis de-escalation', 3, NULL, 2),
('Safety contracting', 3, NULL, 3),
('Emergency resource coordination', 3, NULL, 4),
('Lethal means counseling', 3, NULL, 5),

-- Tier 4: Administrative/Clinical Process
('Coordination of care', 4, NULL, 1),
('Documentation review', 4, NULL, 2),
('Referral discussion', 4, NULL, 3),
('Medication adherence discussion', 4, NULL, 4),
('Homework assignment', 4, NULL, 5)

ON DUPLICATE KEY UPDATE intervention_name=intervention_name; -- No-op if exists

-- ========================================
-- 8. UPDATE APPOINTMENTS TABLE (Add Note Reference)
-- ========================================

ALTER TABLE openemr_postcalendar_events
ADD COLUMN clinical_note_id INT NULL AFTER pc_recurrspec,
ADD INDEX idx_clinical_note (clinical_note_id);

-- Note: Can't add FK constraint to openemr table safely
-- Will enforce relationship in application layer

-- ========================================
-- VERIFICATION QUERIES
-- ========================================

-- Verify tables created
SELECT
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN (
    'clinical_notes',
    'note_drafts',
    'treatment_goals',
    'intervention_library',
    'user_favorite_interventions',
    'clinical_settings'
)
ORDER BY TABLE_NAME;

-- Verify intervention library seeded
SELECT
    intervention_tier,
    modality,
    COUNT(*) as count
FROM intervention_library
GROUP BY intervention_tier, modality
ORDER BY intervention_tier, modality;

-- ========================================
-- NOTES FOR ADMIN
-- ========================================

/*
IMPORTANT NOTES:

1. The clinical_notes table is the primary entity.
   - Notes can exist without appointments
   - Appointments reference notes (via clinical_note_id)

2. Auto-save is handled by note_drafts table
   - Drafts save every 3 seconds to this table
   - When note is officially saved, draft is linked

3. Intervention library is pre-seeded with common interventions
   - Tier 1: Always visible (12 core interventions)
   - Tier 2: Modality-specific (collapsible)
   - Tier 3: Crisis/Risk (triggered by risk flag)
   - Tier 4: Administrative (secondary category)

4. Settings table controls system behavior
   - default_note_template: BIRP or PIRP
   - require_supervisor_review: true/false
   - auto_lock_notes_after_days: number
   - allow_post_signature_edits: true/false

5. UUID field (note_uuid) is for API security
   - Internal ID is auto-increment
   - External references use UUID
   - Prevents ID enumeration attacks

6. All timestamps are auto-managed
   - created_at on INSERT
   - updated_at on UPDATE
   - Audit trail preserved
*/
