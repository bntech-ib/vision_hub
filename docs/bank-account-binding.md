# Bank Account Binding Feature

## Overview

This feature allows users to bind their bank account details to their profile. Once bound, these details cannot be updated or changed, ensuring the security and integrity of financial information.

## Implementation Details

### Database Changes

A new migration was added to the users table with the following fields:

- `bank_account_holder_name` - Name of the account holder
- `bank_account_number` - Bank account number
- `bank_name` - Name of the bank
- `bank_branch` - Branch name (optional)
- `bank_routing_number` - Routing number (optional)
- `bank_account_verified` - Verification status (boolean)
- `bank_account_bound_at` - Timestamp when account was bound

### Model Implementation

The `User` model includes the following methods:

1. `hasBoundBankAccount()` - Checks if the user has already bound their bank account
2. `bindBankAccount(array $bankDetails)` - Binds bank account details to the user (can only be done once)
3. Overrides for `fill()` and `update()` methods to prevent updating bank account fields after they've been bound

### Controller Implementation

The `API\UserProfileController` handles the following endpoints:

1. `GET /api/v1/user/profile` - Get user profile including bank account status
2. `PUT /api/v1/user/profile` - Update user profile (excluding bank account details if already bound)
3. `POST /api/v1/user/bank-account/bind` - Bind bank account details (can only be done once)

### Security Features

1. **Immutable Bank Details**: Once bank account details are bound, they cannot be updated through the API
2. **Hidden Fields**: Sensitive fields like `bank_account_number` and `bank_routing_number` are hidden from serialization
3. **Validation**: Required fields are validated before binding
4. **Authentication**: All endpoints require Sanctum authentication

## Usage

### Binding Bank Account Details

```javascript
// POST /api/v1/user/bank-account/bind
{
  "bank_account_holder_name": "John Doe",
  "bank_account_number": "1234567890",
  "bank_name": "Example Bank",
  "bank_branch": "Main Branch",
  "bank_routing_number": "987654321"
}
```

### Getting User Profile

```javascript
// GET /api/v1/user/profile
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "country": "US",
      "has_bound_bank_account": true,
      "bank_account_bound_at": "2025-10-03T10:30:00.000000Z",
      "bank_account_holder_name": "John Doe",
      "bank_name": "Example Bank",
      "bank_branch": "Main Branch"
    }
  }
}
```

### Updating User Profile

When updating the user profile after binding bank account details, any bank account fields in the request will be ignored:

```javascript
// PUT /api/v1/user/profile
{
  "name": "John Smith",  // This will be updated
  "email": "johnsmith@example.com",  // This will be updated
  "bank_account_holder_name": "Jane Doe"  // This will be ignored
}
```

## Testing

The feature includes comprehensive tests in `tests\Feature\BankAccountBindingTest.php`:

1. `test_user_can_bind_bank_account_details` - Verifies that users can bind their bank account details
2. `test_user_cannot_update_bank_account_after_binding` - Ensures users cannot bind different details after initial binding
3. `test_bank_account_fields_protected_from_profile_updates` - Confirms that bank account fields are protected from profile updates after binding

## Error Handling

- If a user tries to bind bank account details after they've already been bound, they'll receive a 422 error with the message "Bank account details have already been bound and cannot be updated."
- If required fields are missing during binding, standard Laravel validation errors will be returned.