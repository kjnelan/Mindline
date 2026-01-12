-- Add current_medications column to clinical_notes table
-- This stores the structured medication list from Intake forms

ALTER TABLE clinical_notes
ADD COLUMN current_medications TEXT DEFAULT NULL
AFTER diagnosis_codes;
