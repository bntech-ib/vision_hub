-- SQL Script to remove package upgrade tracking fields from users table
-- This script removes the new fields created on 2025-10-06

-- Remove foreign key constraint for previous_package_id
ALTER TABLE `users` DROP FOREIGN KEY `users_previous_package_id_foreign`;

-- Remove the added fields from users table
ALTER TABLE `users` DROP COLUMN `last_package_upgrade_at`;
ALTER TABLE `users` DROP COLUMN `previous_package_id`;

-- Remove the added fields from access_keys table
ALTER TABLE `access_keys` DROP COLUMN `upgrade_requested_at`;
ALTER TABLE `access_keys` DROP COLUMN `upgrade_request_ip`;
ALTER TABLE `access_keys` DROP COLUMN `upgrade_request_user_agent`;

-- Remove the added field from withdrawal_requests table
ALTER TABLE `withdrawal_requests` DROP COLUMN `payment_method_id`;

-- Remove index if it exists
-- DROP INDEX `withdrawal_requests_payment_method_id_index` ON `withdrawal_requests`;