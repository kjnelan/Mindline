-- Migration: Add configurable appointment statuses and cancellation reasons
-- This changes status from ENUM to VARCHAR and seeds the settings_lists table
-- Run this SQL on your database

-- Step 1: Change status column from ENUM to VARCHAR(50)
ALTER TABLE appointments
MODIFY COLUMN status VARCHAR(50) DEFAULT 'scheduled';

-- Step 2: Add appointment statuses to settings_lists
INSERT INTO settings_lists (list_id, option_id, title, notes, is_active, is_default, sort_order) VALUES
('appointment_statuses', 'scheduled', 'Scheduled', 'Appointment is scheduled but not yet confirmed', 1, 1, 1),
('appointment_statuses', 'confirmed', 'Confirmed', 'Appointment has been confirmed by client', 1, 0, 2),
('appointment_statuses', 'arrived', 'Arrived', 'Client has arrived for appointment', 1, 0, 3),
('appointment_statuses', 'in_session', 'In Session', 'Appointment is currently in progress', 1, 0, 4),
('appointment_statuses', 'completed', 'Completed', 'Appointment has been completed', 1, 0, 5),
('appointment_statuses', 'cancelled', 'Cancelled', 'Appointment was cancelled', 1, 0, 6),
('appointment_statuses', 'no_show', 'No Show', 'Client did not show up for appointment', 1, 0, 7)
ON DUPLICATE KEY UPDATE title = VALUES(title), notes = VALUES(notes);

-- Step 3: Add cancellation reasons to settings_lists
INSERT INTO settings_lists (list_id, option_id, title, notes, is_active, is_default, sort_order) VALUES
('cancellation_reasons', 'no_show', 'No Show', 'Client did not show up and did not call', 1, 0, 1),
('cancellation_reasons', 'client_cancelled', 'Client Cancelled', 'Client requested cancellation', 1, 0, 2),
('cancellation_reasons', 'client_cancelled_late', 'Client Cancelled (Late)', 'Client cancelled within 24 hours', 1, 0, 3),
('cancellation_reasons', 'provider_cancelled', 'Provider Cancelled', 'Provider needed to cancel', 1, 0, 4),
('cancellation_reasons', 'emergency', 'Emergency', 'Emergency situation', 1, 0, 5),
('cancellation_reasons', 'illness', 'Illness', 'Client or provider illness', 1, 0, 6),
('cancellation_reasons', 'rescheduled', 'Rescheduled', 'Appointment was rescheduled to another time', 1, 0, 7),
('cancellation_reasons', 'insurance_issue', 'Insurance Issue', 'Insurance authorization or coverage issue', 1, 0, 8),
('cancellation_reasons', 'transportation', 'Transportation', 'Client had transportation issues', 1, 0, 9),
('cancellation_reasons', 'weather', 'Weather', 'Inclement weather conditions', 1, 0, 10),
('cancellation_reasons', 'other', 'Other', 'Other reason - see notes', 1, 0, 99)
ON DUPLICATE KEY UPDATE title = VALUES(title), notes = VALUES(notes);
