-- Add CPT billing-related fields to appointment_categories table

ALTER TABLE appointment_categories
ADD COLUMN category_type ENUM('client', 'clinic', 'holiday') DEFAULT 'client'
AFTER is_billable;

ALTER TABLE appointment_categories
ADD COLUMN requires_cpt_selection TINYINT(1) DEFAULT 0
AFTER category_type;

ALTER TABLE appointment_categories
ADD COLUMN blocks_availability TINYINT(1) DEFAULT 0
AFTER requires_cpt_selection;

-- Add indexes for performance
CREATE INDEX idx_category_type ON appointment_categories(category_type);
CREATE INDEX idx_requires_cpt ON appointment_categories(requires_cpt_selection);

-- Update existing categories to have appropriate category_type
-- By default, all existing categories are for client appointments
UPDATE appointment_categories
SET category_type = 'client'
WHERE category_type IS NULL OR category_type = 'client';
