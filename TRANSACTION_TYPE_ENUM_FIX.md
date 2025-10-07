# Fix for Transaction Type ENUM Mismatch Error

## Issue Description
The error "SQLSTATE[01000]: Warning: 1265 Data truncated for column 'type' at row 1" was occurring when trying to reject withdrawals because there was a mismatch between the code and database schema:

- Code was trying to insert a transaction with type 'withdrawal_refund'
- Database schema only allowed ['earning', 'purchase', 'withdrawal', 'refund']

## Root Cause
The transactions table migration defined the type column with ENUM values ['earning', 'purchase', 'withdrawal', 'refund'], but the application code was trying to insert transactions with type 'withdrawal_refund', which was not in the allowed list.

## Solution Implemented

### 1. Code Updates
Updated the following files to use the correct transaction type:
- `app/Http/Controllers/Admin/WithdrawalController.php` - Changed transaction type from 'withdrawal_refund' to 'refund' in the reject method
- `tests/Feature/WithdrawalSystemTest.php` - Updated test assertion to expect 'refund' instead of 'withdrawal_refund'

## Files Modified
1. `app/Http/Controllers/Admin/WithdrawalController.php` - Transaction type value
2. `tests/Feature/WithdrawalSystemTest.php` - Test assertion

## Benefits
1. **Fixed the Error**: Resolved the SQL truncation error
2. **Maintained Consistency**: Used the existing 'refund' type which is more appropriate
3. **Backward Compatibility**: Existing records with 'refund' type still work
4. **Standards Compliance**: Used the standard transaction types defined in the database schema

## Testing
The fix has been implemented to ensure:
- Withdrawals can be rejected without SQL errors
- Refund transactions are properly created with the correct type
- All existing functionality remains intact
- Database schema and application code are now consistent