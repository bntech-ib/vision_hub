# Make Transaction ID Optional for Withdrawal Approval

## Issue Description
Previously, admins were required to enter a transaction ID when approving withdrawals, which was unnecessary and created friction in the approval process.

## Changes Made

### 1. Controller Update
Modified `app/Http/Controllers/Admin/WithdrawalController.php` in the `approve` method:
- Kept the transaction_id validation as `nullable|string|max:255` (already was nullable)
- Updated the logic to automatically generate a transaction ID if none is provided:
  ```php
  'transaction_id' => $validated['transaction_id'] ?? 'withdrawal_' . $withdrawal->id
  ```

### 2. View Update
Modified `resources/views/admin/withdrawals/index.blade.php`:
- Changed the label from "Transaction ID" to "Transaction ID (Optional)"
- Added helper text: "Enter transaction ID if available. If left blank, a system-generated ID will be used."

### 3. Show Page Enhancement
Modified `resources/views/admin/withdrawals/show.blade.php`:
- Added conditional display of Transaction ID field only when it exists

## Benefits
1. **Simplified Workflow**: Admins can now approve withdrawals without needing to provide a transaction ID
2. **Automatic Generation**: System automatically generates a transaction ID in the format `withdrawal_{id}` if none is provided
3. **Backward Compatibility**: Still supports manual transaction ID entry when needed
4. **Better UX**: Clearer labeling indicates the field is optional

## Technical Details
The system now:
- Automatically generates transaction IDs as `withdrawal_{withdrawal_id}` when none is provided
- Maintains all existing functionality for manually entered transaction IDs
- Properly displays transaction IDs in the withdrawal details page
- Ensures data consistency across the withdrawal approval process

## Testing
The changes have been implemented to ensure:
- Withdrawals can be approved without entering a transaction ID
- System-generated transaction IDs are properly stored
- Manually entered transaction IDs are still accepted
- All existing functionality remains intact