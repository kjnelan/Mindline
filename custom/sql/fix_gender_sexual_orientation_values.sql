-- Fix patient demographics: replace display titles with proper SNOMED codes
-- This fixes dropdowns showing "Select..." when they should show current value

-- Gender Identity - Map titles to SNOMED codes
UPDATE patient_data SET gender_identity = '446151000124109' WHERE gender_identity = 'Identifies as Male';
UPDATE patient_data SET gender_identity = '446141000124107' WHERE gender_identity = 'Identifies as Female';
UPDATE patient_data SET gender_identity = '407377005' WHERE gender_identity LIKE '%Female-to-Male%' OR gender_identity LIKE '%FTM%';
UPDATE patient_data SET gender_identity = '407376001' WHERE gender_identity LIKE '%Male-to-Female%' OR gender_identity LIKE '%MTF%';
UPDATE patient_data SET gender_identity = '446131000124102' WHERE gender_identity LIKE '%Genderqueer%';
UPDATE patient_data SET gender_identity = 'comment_OTH' WHERE gender_identity LIKE '%Additional gender%' OR gender_identity LIKE '%other%';
UPDATE patient_data SET gender_identity = 'ASKU' WHERE gender_identity = 'Choose not to disclose';

-- Sexual Orientation - Map titles to codes (check your list_options for exact codes)
-- Run this query first to see your sexual_orientation codes:
-- SELECT option_id, title FROM list_options WHERE list_id = 'sexual_orientation' ORDER BY seq;

-- Example mappings (update with your actual option_ids):
UPDATE patient_data SET sexual_orientation = '20430005' WHERE sexual_orientation = 'Straight or heterosexual';
UPDATE patient_data SET sexual_orientation = '38628009' WHERE sexual_orientation LIKE '%Lesbian%';
UPDATE patient_data SET sexual_orientation = '42035005' WHERE sexual_orientation LIKE '%Gay%';
UPDATE patient_data SET sexual_orientation = '42035005' WHERE sexual_orientation LIKE '%Bisexual%';
UPDATE patient_data SET sexual_orientation = 'comment_OTH' WHERE sexual_orientation LIKE '%other%' OR sexual_orientation LIKE '%Something else%';
UPDATE patient_data SET sexual_orientation = 'ASKU' WHERE sexual_orientation = 'Choose not to disclose';

-- Verify the changes
SELECT pid, fname, lname, gender_identity, sexual_orientation
FROM patient_data
WHERE gender_identity IS NOT NULL OR sexual_orientation IS NOT NULL
ORDER BY pid;
