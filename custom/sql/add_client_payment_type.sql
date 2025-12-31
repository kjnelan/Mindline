-- Add 'client' option to payment_type list for self-pay clients
-- This is used by the Insurance tab to detect self-pay clients

-- Check if 'client' option already exists
-- If not, add it to the payment_type list

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `activity`)
VALUES ('payment_type', 'client', 'Self-Pay (Client)', 30, 0, 1)
ON DUPLICATE KEY UPDATE
  `title` = 'Self-Pay (Client)',
  `seq` = 30,
  `activity` = 1;

-- Verify the insertion
SELECT * FROM list_options WHERE list_id = 'payment_type' ORDER BY seq;
