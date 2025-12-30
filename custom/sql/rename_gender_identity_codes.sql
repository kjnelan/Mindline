-- Rename gender_identity option_ids from SNOMED codes to simple codes
-- Backup first: mysqldump -u root openemr list_options > list_options_backup.sql

-- Gender Identity: Change SNOMED codes to simple codes
UPDATE list_options SET option_id = 'male' WHERE list_id = 'gender_identity' AND option_id = '446151000124109';
UPDATE list_options SET option_id = 'female' WHERE list_id = 'gender_identity' AND option_id = '446141000124107';
UPDATE list_options SET option_id = 'trans_male' WHERE list_id = 'gender_identity' AND option_id = '407377005';
UPDATE list_options SET option_id = 'trans_female' WHERE list_id = 'gender_identity' AND option_id = '407376001';
UPDATE list_options SET option_id = 'non_binary' WHERE list_id = 'gender_identity' AND option_id = '446131000124102';
UPDATE list_options SET option_id = 'other' WHERE list_id = 'gender_identity' AND option_id = 'comment_OTH';
UPDATE list_options SET option_id = 'decline' WHERE list_id = 'gender_identity' AND option_id = 'ASKU';

-- Now update patient_data to use new codes
UPDATE patient_data SET gender_identity = 'male' WHERE gender_identity = '446151000124109';
UPDATE patient_data SET gender_identity = 'female' WHERE gender_identity = '446141000124107';
UPDATE patient_data SET gender_identity = 'trans_male' WHERE gender_identity = '407377005';
UPDATE patient_data SET gender_identity = 'trans_female' WHERE gender_identity = '407376001';
UPDATE patient_data SET gender_identity = 'non_binary' WHERE gender_identity = '446131000124102';
UPDATE patient_data SET gender_identity = 'other' WHERE gender_identity = 'comment_OTH';
UPDATE patient_data SET gender_identity = 'decline' WHERE gender_identity = 'ASKU';

-- Also catch any that still have old text values
UPDATE patient_data SET gender_identity = 'male' WHERE gender_identity = 'Identifies as Male';
UPDATE patient_data SET gender_identity = 'female' WHERE gender_identity = 'Identifies as Female';
UPDATE patient_data SET gender_identity = 'decline' WHERE gender_identity = 'Choose not to disclose';

-- Verify changes
SELECT option_id, title, seq FROM list_options WHERE list_id = 'gender_identity' ORDER BY seq;
SELECT pid, fname, lname, gender_identity FROM patient_data WHERE gender_identity IS NOT NULL;
