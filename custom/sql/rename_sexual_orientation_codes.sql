-- Rename sexual_orientation option_ids from SNOMED codes to simple codes
-- Backup first: mysqldump -u root openemr list_options > list_options_backup.sql

-- First, check what codes you currently have:
SELECT option_id, title, seq FROM list_options WHERE list_id = 'sexual_orientation' ORDER BY seq;

-- Sexual Orientation: Change SNOMED codes to simple codes
-- UPDATE these based on what you see in the SELECT above
UPDATE list_options SET option_id = 'straight' WHERE list_id = 'sexual_orientation' AND option_id = '20430005';
UPDATE list_options SET option_id = 'lesbian' WHERE list_id = 'sexual_orientation' AND option_id = '38628009';
UPDATE list_options SET option_id = 'gay' WHERE list_id = 'sexual_orientation' AND option_id = '42035005';
UPDATE list_options SET option_id = 'bisexual' WHERE list_id = 'sexual_orientation' AND (title LIKE '%Bisexual%' OR title LIKE '%bisexual%');
UPDATE list_options SET option_id = 'other' WHERE list_id = 'sexual_orientation' AND option_id = 'comment_OTH';
UPDATE list_options SET option_id = 'decline' WHERE list_id = 'sexual_orientation' AND option_id = 'ASKU';

-- Now update patient_data to use new codes
UPDATE patient_data SET sexual_orientation = 'straight' WHERE sexual_orientation = '20430005';
UPDATE patient_data SET sexual_orientation = 'lesbian' WHERE sexual_orientation = '38628009';
UPDATE patient_data SET sexual_orientation = 'gay' WHERE sexual_orientation = '42035005';
UPDATE patient_data SET sexual_orientation = 'decline' WHERE sexual_orientation = 'ASKU';

-- Also catch any that still have old text values
UPDATE patient_data SET sexual_orientation = 'straight' WHERE sexual_orientation LIKE '%Straight%' OR sexual_orientation LIKE '%heterosexual%';
UPDATE patient_data SET sexual_orientation = 'lesbian' WHERE sexual_orientation LIKE '%Lesbian%';
UPDATE patient_data SET sexual_orientation = 'gay' WHERE sexual_orientation LIKE '%Gay%';
UPDATE patient_data SET sexual_orientation = 'bisexual' WHERE sexual_orientation LIKE '%Bisexual%';
UPDATE patient_data SET sexual_orientation = 'decline' WHERE sexual_orientation = 'Choose not to disclose';

-- Verify changes
SELECT option_id, title, seq FROM list_options WHERE list_id = 'sexual_orientation' ORDER BY seq;
SELECT pid, fname, lname, sexual_orientation FROM patient_data WHERE sexual_orientation IS NOT NULL;
