-- Deactivate Holiday, Vacation, and Out of Office categories
-- These should be managed in user availability/block time settings, not as appointments

UPDATE appointment_categories
SET is_active = 0
WHERE name IN ('Holiday', 'Vacation', 'Out of Office');
