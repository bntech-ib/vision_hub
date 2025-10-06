-- SQL Script to update existing withdrawal requests with payment_method_id
-- This script updates the withdrawal requests to use the new payment_method_id field

-- Set payment_method_id = 1 for wallet balance withdrawals (default)
-- Assuming that previously, withdrawals without a specific referral indication were from wallet balance
UPDATE `withdrawal_requests` 
SET `payment_method_id` = 1 
WHERE `payment_method_id` = 0 OR `payment_method_id` IS NULL;

-- If you have a way to identify referral earnings withdrawals in your existing data,
-- you can update them like this:
-- UPDATE `withdrawal_requests` 
-- SET `payment_method_id` = 2 
-- WHERE [your condition to identify referral earnings withdrawals];

-- Add a comment field to track the update
-- ALTER TABLE `withdrawal_requests` ADD COLUMN `update_comment` VARCHAR(255) NULL DEFAULT NULL AFTER `payment_method_id`;

-- Verification query to check the updates
SELECT 
    id,
    user_id,
    amount,
    payment_method_id,
    status,
    created_at
FROM `withdrawal_requests` 
ORDER BY created_at DESC 
LIMIT 10;

-- Count of withdrawals by payment method
SELECT 
    payment_method_id,
    COUNT(*) as count,
    SUM(amount) as total_amount
FROM `withdrawal_requests` 
GROUP BY payment_method_id;