-- Add default appointment categories

INSERT IGNORE INTO appointment_categories (name, description, color, default_duration, is_billable, category_type, requires_cpt_selection, blocks_availability, is_active, sort_order, created_at, updated_at)
VALUES
-- Client appointment categories (billable)
('Individual Therapy', 'Individual psychotherapy session', '#3B82F6', 50, 1, 'client', 1, 0, 1, 1, NOW(), NOW()),
('Family Therapy', 'Family psychotherapy session', '#10B981', 60, 1, 'client', 1, 0, 1, 2, NOW(), NOW()),
('Group Therapy', 'Group psychotherapy session', '#8B5CF6', 90, 1, 'client', 1, 0, 1, 3, NOW(), NOW()),
('Initial Intake', 'Initial evaluation and assessment', '#F59E0B', 90, 1, 'client', 1, 0, 1, 4, NOW(), NOW()),
('Crisis Intervention', 'Crisis intervention session', '#EF4444', 50, 1, 'client', 1, 0, 1, 5, NOW(), NOW()),

-- Clinic/internal categories (non-billable)
('Staff Meeting', 'Internal staff meeting', '#6B7280', 60, 0, 'clinic', 0, 1, 1, 10, NOW(), NOW()),
('Supervision', 'Clinical supervision', '#06B6D4', 60, 0, 'clinic', 0, 0, 1, 11, NOW(), NOW()),
('Training', 'Staff training session', '#84CC16', 120, 0, 'clinic', 0, 1, 1, 12, NOW(), NOW()),

-- Holiday/closure categories (blocks availability)
('Holiday', 'Office closed for holiday', '#DC2626', 480, 0, 'holiday', 0, 1, 1, 20, NOW(), NOW()),
('Vacation', 'Provider vacation/time off', '#F97316', 480, 0, 'holiday', 0, 1, 1, 21, NOW(), NOW()),
('Out of Office', 'Provider out of office', '#A855F7', 480, 0, 'holiday', 0, 1, 1, 22, NOW(), NOW());
