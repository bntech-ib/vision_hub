# Withdrawal System Update

## Overview
This update implements the proper withdrawal system flow as requested:
1. When a user requests a withdrawal, the amount is immediately deducted from their chosen source (wallet balance or referral earnings)
2. When an admin approves a withdrawal, no additional deduction occurs (to avoid duplicate deduction)
3. When an admin rejects a withdrawal, the amount is refunded to the user's original source

## Changes Made

### 1. API TransactionController - requestWithdrawal Method
- Modified to deduct the withdrawal amount immediately when the user submits the request
- Added transaction record for the deduction with status 'pending'
- Used database transactions to ensure data consistency

### 2. Admin WithdrawalController - approve Method
- Removed the duplicate deduction logic that was previously in place
- Updated the transaction record status from 'pending' to 'completed'
- Added proper transaction ID handling

### 3. Admin WithdrawalController - reject Method
- Added logic to refund the amount to the user's original source
- Created a new transaction record for the refund with type 'withdrawal_refund'
- Updated the original transaction record status to 'refunded'
- Added descriptive messages explaining what happened

## How It Works

### Withdrawal Request Flow
1. User submits withdrawal request specifying amount and payment method (1=wallet, 2=referral earnings)
2. System validates user has sufficient balance
3. System immediately deducts the amount from the chosen source
4. System creates a withdrawal request record with status 'pending'
5. System creates a transaction record with status 'pending'

### Admin Approval Flow
1. Admin reviews pending withdrawal request
2. Admin clicks "Approve"
3. System updates withdrawal request status to 'approved'
4. System updates transaction record status to 'completed'
5. No additional deduction occurs (amount already deducted)

### Admin Rejection Flow
1. Admin reviews pending withdrawal request
2. Admin clicks "Reject" and provides a reason
3. System refunds the amount to the user's original source
4. System creates a new transaction record for the refund
5. System updates the original transaction record status to 'refunded'
6. System updates withdrawal request status to 'rejected'

## Benefits
- Prevents duplicate deductions when admin approves withdrawals
- Ensures users get their money back when withdrawals are rejected
- Maintains accurate transaction records for auditing
- Provides clear status tracking for all withdrawal operations

## Testing
The system has been tested with various scenarios:
- Successful withdrawal requests
- Approval of pending withdrawals
- Rejection of pending withdrawals
- Insufficient balance checks
- Edge cases with different payment methods

All tests pass successfully, confirming the implementation works as expected.