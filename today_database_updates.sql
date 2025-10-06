-- Complete SQL Script for Today's Database Updates (2025-10-06)
-- This script contains all the database changes made today

-- 1. Add package upgrade tracking fields to users table
-- Add last_package_upgrade_at field to track the last package upgrade timestamp
ALTER TABLE `users` ADD COLUMN `last_package_upgrade_at` TIMESTAMP NULL DEFAULT NULL AFTER `package_expires_at`;

-- Add previous_package_id field to track the previous package ID for audit purposes
ALTER TABLE `users` ADD COLUMN `previous_package_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `current_package_id`;

-- Add foreign key constraint for previous_package_id
ALTER TABLE `users` ADD CONSTRAINT `users_previous_package_id_foreign` 
FOREIGN KEY (`previous_package_id`) REFERENCES `user_packages` (`id`) ON DELETE SET NULL;

-- 2. Add upgrade tracking fields to access_keys table
ALTER TABLE `access_keys` ADD COLUMN `upgrade_requested_at` TIMESTAMP NULL DEFAULT NULL AFTER `used_at`;
ALTER TABLE `access_keys` ADD COLUMN `upgrade_request_ip` VARCHAR(45) NULL DEFAULT NULL AFTER `upgrade_requested_at`;
ALTER TABLE `access_keys` ADD COLUMN `upgrade_request_user_agent` TEXT NULL DEFAULT NULL AFTER `upgrade_request_ip`;

-- 3. Add payment_method_id field to withdrawal_requests table
ALTER TABLE `withdrawal_requests` ADD COLUMN `payment_method_id` INT NOT NULL DEFAULT '1' AFTER `user_id`;

-- 4. Add index for better query performance on payment_method_id
ALTER TABLE `withdrawal_requests` ADD INDEX `withdrawal_requests_payment_method_id_index` (`payment_method_id`);

-- 5. Update existing withdrawal requests to set payment_method_id
-- payment_method_id = 1 for wallet balance (default)
-- payment_method_id = 2 for referral earnings
UPDATE `withdrawal_requests` SET `payment_method_id` = 1 WHERE `payment_method_id` = 0;

-- 6. Verification queries to confirm the changes
-- Users table verification
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'users' AND COLUMN_NAME IN ('last_package_upgrade_at', 'previous_package_id');

-- Access keys table verification
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'access_keys' AND COLUMN_NAME IN ('upgrade_requested_at', 'upgrade_request_ip', 'upgrade_request_user_agent');

-- Withdrawal requests table verification
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'withdrawal_requests' AND COLUMN_NAME = 'payment_method_id';

-- Sample data verification
SELECT 
    id,
    user_id,
    amount,
    payment_method_id,
    status,
    created_at
FROM `withdrawal_requests` 
ORDER BY created_at DESC 
LIMIT 5;