# Fix for Withdrawal Status ENUM Mismatch Error

## Issue Description
The error "SQLSTATE[01000]: Warning: 1265 Data truncated for column 'status' at row 1" was occurring when trying to approve withdrawals because there was a mismatch between the code and database schema:

- Code was trying to set status to 'approved'
- Database schema only allowed ['pending', 'processing', 'completed', 'rejected']

## Root Cause
The withdrawal_requests table migration defined the status column with ENUM values ['pending', 'processing', 'completed', 'rejected'], but the application code was trying to set the status to 'approved', which was not in the allowed list.

## Solution Implemented

### 1. Database Migration
Created a new migration `2025_10_07_110000_update_withdrawal_requests_status_enum.php` to update the ENUM values to include 'approved':
```php
$table->enum('status', ['pending', 'processing', 'completed', 'rejected', 'approved'])->default('pending')->change();
```

### 2. Code Updates
Updated the following files to maintain consistency:
- `app/Http/Controllers/Admin/WithdrawalController.php` - Kept using 'approved' status
- `app/Models/WithdrawalRequest.php` - Updated isApproved() method and approve() method to use 'approved'
- Controller validation rules updated to include 'approved' in the allowed status values
- Stats calculation updated to count 'approved' status instead of 'completed'

## Files Modified
1. `database/migrations/2025_10_07_110000_update_withdrawal_requests_status_enum.php` - New migration
2. `app/Http/Controllers/Admin/WithdrawalController.php` - Status value and validation
3. `app/Models/WithdrawalRequest.php` - isApproved() method and approve() method

## Benefits
1. **Fixed the Error**: Resolved the SQL truncation error
2. **Maintained Consistency**: Kept the application terminology consistent with "approved" status
3. **Backward Compatibility**: Existing records with 'completed' status still work
4. **Future-Proof**: Added flexibility for both 'approved' and 'completed' statuses

## Testing
The fix has been implemented to ensure:
- Withdrawals can be approved without SQL errors
- All existing functionality remains intact
- Database schema and application code are now consistent