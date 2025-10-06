# Withdrawal Deduction System - Implementation Summary

## Overview
The withdrawal system has been successfully implemented to deduct funds from the selected withdrawal method (wallet balance or referral earnings) when an admin approves a withdrawal request.

## Key Implementation Details

### 1. Payment Method Identification
- **payment_method_id = 1**: Wallet Balance
- **payment_method_id = 2**: Referral Earnings

### 2. Balance Validation
Before approving any withdrawal, the system validates that the user has sufficient funds in the selected withdrawal method:
- For wallet balance withdrawals: Checks `user->wallet_balance`
- For referral earnings withdrawals: Checks `user->referral_earnings`

### 3. Deduction Logic
The system uses specific methods in the User model to deduct funds:
- **deductFromWallet(float $amount)**: Deducts from wallet balance
- **deductFromReferralEarnings(float $amount)**: Deducts from referral earnings

Both methods:
- Verify sufficient balance before deduction
- Return `true` on success, `false` on failure
- Use database `decrement()` method for atomic operations

### 4. Transaction Recording
When a withdrawal is approved, the system creates a transaction record with:
- `type`: 'withdrawal'
- `amount`: Negative value (e.g., -100 for a 100 withdrawal)
- `description`: "Withdrawal approved - [Wallet Balance|Referral Earnings]"
- `status`: 'completed'
- `reference_type`: WithdrawalRequest class
- `reference_id`: Withdrawal request ID

### 5. Approval Process Flow
1. Admin approves withdrawal request via `/api/admin/withdrawals/{id}/approve`
2. System validates user has sufficient balance in selected method
3. System deducts amount from the appropriate balance
4. System creates transaction record
5. System updates withdrawal request status to 'approved'

## Code Implementation

### Admin Withdrawal Controller
File: `app/Http/Controllers/Admin/WithdrawalController.php`

Key methods:
- **approve()**: Handles withdrawal approval and deduction
- Validates balance based on `payment_method_id`
- Calls appropriate deduction method
- Creates transaction record
- Updates withdrawal status

### User Model Methods
File: `app/Models/User.php`

Key methods:
- **deductFromWallet(float $amount)**: Deducts from wallet balance
- **deductFromReferralEarnings(float $amount)**: Deducts from referral earnings

### Database Schema
Table: `withdrawal_requests`
- Added `payment_method_id` column to identify deduction source
- Added index for better query performance

## Testing
Unit tests have been created to verify:
1. Wallet balance withdrawals are deducted correctly
2. Referral earnings withdrawals are deducted correctly
3. Insufficient balance scenarios are handled properly
4. Transaction records are created with correct information

## API Endpoints

### Request Withdrawal
```
POST /api/v1/wallet/withdraw
{
  "amount": 100,
  "payment_method_id": 1  // 1 = Wallet Balance, 2 = Referral Earnings
}
```

### Approve Withdrawal (Admin)
```
POST /api/admin/withdrawals/{id}/approve
{
  "notes": "Approved for processing",
  "transaction_id": "txn_123456"
}
```

## Security Considerations
- Only admins can approve withdrawals
- Balance validation prevents overdrafts
- Atomic database operations ensure data consistency
- Transaction records provide audit trail