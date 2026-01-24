-- Add CPT billing-related fields to appointment_categories table
-- Safe to run multiple times (idempotent)

-- Add category_type column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = 'appointment_categories';
SET @columnname = 'category_type';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE appointment_categories ADD COLUMN category_type ENUM('client', 'clinic', 'holiday') DEFAULT 'client' AFTER is_billable"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add requires_cpt_selection column if it doesn't exist
SET @columnname = 'requires_cpt_selection';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE appointment_categories ADD COLUMN requires_cpt_selection TINYINT(1) DEFAULT 0 AFTER category_type"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add blocks_availability column if it doesn't exist
SET @columnname = 'blocks_availability';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE (table_name = @tablename)
   AND (table_schema = @dbname)
   AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE appointment_categories ADD COLUMN blocks_availability TINYINT(1) DEFAULT 0 AFTER requires_cpt_selection"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add indexes if they don't exist (using CREATE INDEX IF NOT EXISTS for MariaDB 10.5+)
-- For older versions, these will fail silently if index exists
CREATE INDEX IF NOT EXISTS idx_category_type ON appointment_categories(category_type);
CREATE INDEX IF NOT EXISTS idx_requires_cpt ON appointment_categories(requires_cpt_selection);

-- Update existing categories to have appropriate category_type
-- By default, all existing categories are for client appointments
UPDATE appointment_categories
SET category_type = 'client'
WHERE category_type IS NULL;
