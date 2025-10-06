# Withdrawal System Documentation

## Overview

The withdrawal system allows users to request withdrawals from either their wallet balance or referral earnings. When approved by an admin, the system deducts the requested amount from the selected source and creates a transaction record.

## How It Works

### 1. Withdrawal Request

Users can request withdrawals from either:
- **Wallet Balance** (payment_method_id = 1)
- **Referral Earnings** (payment_method_id = 2)

### 2. Balance Validation

Before approval, the system checks if the user has sufficient balance in the selected withdrawal method:
- For wallet balance withdrawals: Checks `wallet_balance` field
- For referral earnings withdrawals: Checks `referral_earnings` field

### 3. Approval Process

When an admin approves a withdrawal:
1. System validates user has sufficient balance
2. Deducts amount from the selected source:
   - Wallet balance: Uses `deductFromWallet()` method
   - Referral earnings: Uses `deductFromReferralEarnings()` method
3. Creates a transaction record with negative amount
4. Updates withdrawal request status to 'approved'

### 4. Transaction Records

Each approved withdrawal creates a transaction record with:
- `type`: 'withdrawal'
- `amount`: Negative value (e.g., -100 for a 100 withdrawal)
- `description`: "Withdrawal approved - [Wallet Balance|Referral Earnings]"
- `status`: 'completed'
- `reference_type`: WithdrawalRequest class
- `reference_id`: Withdrawal request ID

## API Endpoints

### Request Withdrawal

```
POST /api/v1/wallet/withdraw
```

**Request Body:**
```json
{
  "amount": 100,
  "payment_method_id": 1  // 1 = Wallet Balance, 2 = Referral Earnings
}
```

**Response:**
```json
{
  "success": true,
  "message": "Withdrawal request submitted successfully",
  "withdrawal": {
    "id": 123,
    "user_id": 456,
    "amount": 100,
    "payment_method": "Wallet Balance",
    "payment_method_id": 1,
    "payment_details": {
      "accountName": "John Doe",
      "accountNumber": "1234567890",
      "bankName": "Test Bank"
    },
    "status": "pending",
    "created_at": "2023-01-01T00:00:00.000000Z"
  }
}
```

### Approve Withdrawal (Admin)

```
POST /api/admin/withdrawals/{id}/approve
```

**Request Body:**
```json
{
  "notes": "Approved for processing",
  "transaction_id": "txn_123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Wallet balance withdrawal approved successfully",
  "withdrawal": {
    "id": 123,
    "user_id": 456,
    "amount": 100,
    "payment_method": "Wallet Balance",
    "payment_method_id": 1,
    "status": "approved",
    "processed_at": "2023-01-01T00:00:00.000000Z",
    "processed_by": 789,
    "notes": "Approved for processing",
    "transaction_id": "txn_123456"
  }
}
```

## Database Tables

### withdrawal_requests

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `user_id` | bigint | Foreign key to users table |
| `amount` | decimal | Withdrawal amount |
| `payment_method` | string | 'Wallet Balance' or 'Referral Earnings' |
| `payment_method_id` | int | 1 = Wallet Balance, 2 = Referral Earnings |
| `payment_details` | json | User's bank account details |
| `status` | string | 'pending', 'approved', 'rejected', 'completed' |
| `processed_at` | timestamp | When processed |
| `admin_note` | text | Admin notes |

### transactions

When a withdrawal is approved, a transaction record is created:

| Column | Value |
|--------|-------|
| `user_id` | User ID |
| `type` | 'withdrawal' |
| `amount` | Negative withdrawal amount |
| `description` | "Withdrawal approved - [Wallet Balance|Referral Earnings]" |
| `status` | 'completed' |
| `reference_type` | WithdrawalRequest class |
| `reference_id` | Withdrawal request ID |

## User Model Methods

### Balance Deduction Methods

1. **deductFromWallet(float $amount)**: Deducts amount from wallet balance
2. **deductFromReferralEarnings(float $amount)**: Deducts amount from referral earnings

Both methods return `true` on success and `false` if insufficient balance.

## Implementation Details

### Key Methods

1. **WithdrawalController::approve()** - Handles withdrawal approval and deduction
2. **User::deductFromWallet()** - Deducts from wallet balance
3. **User::deductFromReferralEarnings()** - Deducts from referral earnings

### Withdrawal Flow

1. User submits withdrawal request via API
2. System validates amount and payment method
3. Request is stored with 'pending' status
4. Admin reviews and approves the request
5. System validates user has sufficient balance
6. System deducts amount from selected source
7. Transaction record is created
8. Withdrawal status is updated to 'approved'