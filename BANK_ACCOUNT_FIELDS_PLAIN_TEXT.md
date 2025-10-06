# Bank Account Fields Plain Text Storage

## Overview
This update changes the storage of bank account fields from encrypted to plain text as requested. Previously, bank account information was encrypted before being stored in the database. After this change, all bank account data will be stored in plain text.

## Changes Made

### 1. User Model Modifications
- Modified getter methods in `app/Models/User.php` to return values as-is without decryption
- Modified setter methods in `app/Models/User.php` to store values as-is without encryption

### 2. Migration
- Created migration `2025_10_06_180000_update_bank_account_fields_to_plain_text.php` for documentation purposes

### 3. Data Migration Script
- Created `decrypt_bank_accounts.php` script to decrypt existing encrypted data

## Fields Affected
- `bank_account_holder_name`
- `bank_account_number`
- `bank_name`
- `bank_branch`
- `bank_routing_number`

## Important Notes

### Security Implications
Storing bank account information in plain text reduces security. Ensure that:
1. Database access is properly restricted
2. Regular backups are encrypted
3. Application-level security measures are in place
4. Access to sensitive data is logged and monitored

### Existing Data
Any existing encrypted bank account data will need to be decrypted using the provided script. The script attempts to decrypt each field and update it with the plain text value.

### Migration Process
1. Deploy the code changes
2. Run the `decrypt_bank_accounts.php` script to convert existing data
3. Verify that all data has been properly converted
4. Delete the script after use

## Testing
The changes have been tested to ensure:
- New bank account data is stored in plain text
- Existing encrypted data can be decrypted and converted
- All application functionality continues to work as expected

## Rollback
If you need to revert to encrypted storage:
1. Restore the original getter and setter methods in the User model
2. Re-encrypt existing plain text data (no automated script provided)
3. Update any related documentation