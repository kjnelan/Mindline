-- Add default rooms/locations to settings_lists table

INSERT IGNORE INTO settings_lists (list_id, option_id, title, notes, sort_order, is_active, is_default, created_at, updated_at)
VALUES
('rooms', 'office1', 'Office 1', 'Main therapy office', 1, 1, 1, NOW(), NOW()),
('rooms', 'office2', 'Office 2', 'Secondary therapy office', 2, 1, 0, NOW(), NOW()),
('rooms', 'office3', 'Office 3', 'Group therapy room', 3, 1, 0, NOW(), NOW()),
('rooms', 'telehealth', 'Telehealth', 'Virtual/remote session', 4, 1, 0, NOW(), NOW()),
('rooms', 'conference', 'Conference Room', 'Large meeting space', 5, 1, 0, NOW(), NOW());
