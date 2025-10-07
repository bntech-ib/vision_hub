# Fix for Transaction Status ENUM Mismatch Error

## Issue Description
The error "SQLSTATE[01000]: Warning: 1265 Data truncated for column 'status' at row 1" was occurring when trying to reject withdrawals because there was a mismatch between the code and database schema:

- Code was trying to update a transaction with status 'refunded'
- Database schema only allowed ['pending', 'completed', 'failed', 'cancelled']

## Root Cause
The transactions table migration defined the status column with ENUM values ['pending', 'completed', 'failed', 'cancelled'], but the application code was trying to update transactions with status 'refunded' and 'partial_refund', which were not in the allowed list.

## Solution Implemented

### 1. Database Migration
Created a new migration `2025_10_07_111000_update_transactions_status_enum.php` to update the ENUM values to include 'refunded' and 'partial_refund':
```php
$table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded', 'partial_refund'])->default('pending')->change();
```

### 2. Code Consistency
The application code was already using 'refunded' and 'partial_refund' statuses in several places:
- TransactionController for refund operations
- Transaction index view for filtering
- Withdrawal rejection logic
- Various tests and documentation

## Files Modified
1. `database/migrations/2025_10_07_111000_update_transactions_status_enum.php` - New migration

## Benefits
1. **Fixed the Error**: Resolved the SQL truncation error
2. **Maintained Consistency**: Kept the application terminology consistent with existing code
3. **Backward Compatibility**: Existing records with original status values still work
4. **Future-Proof**: Added flexibility for all required transaction statuses

## Testing
The fix has been implemented to ensure:
- Withdrawals can be rejected without SQL errors
- Transaction refunds work properly with the correct status values
- All existing functionality remains intact
- Database schema and application code are now consistent