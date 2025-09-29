# Withdrawal Access Control Implementation

## Overview
This implementation adds admin-controlled withdrawal access for users in the VisionHub platform. Users cannot withdraw funds unless an admin explicitly enables withdrawal access for them.

## Features Implemented

### 1. Database Migration
- Added `withdrawal_enabled` boolean field to the `users` table
- Default value is `true` to maintain backward compatibility
- Migration file: `2025_09_07_100000_add_withdrawal_enabled_to_users_table.php`

### 2. User Model Enhancement
- Added `withdrawal_enabled` to the `$fillable` array for mass assignment
- Added `hasWithdrawalAccess()` method to check if user can withdraw
- Method properly checks the database value and defaults to `true` if column doesn't exist

### 3. API Controller Update
- Modified `TransactionController::requestWithdrawal()` to check withdrawal access
- Returns 403 Forbidden response with clear error message if access is disabled
- Error message: "Withdrawal access has been disabled by admin. Please contact support for assistance."

### 4. Admin Interface
- Added `enableWithdrawal()` and `disableWithdrawal()` methods to `UserController`
- Added routes for enabling/disabling withdrawal access:
  - `PUT /admin/users/{user}/enable-withdrawal`
  - `PUT /admin/users/{user}/disable-withdrawal`
- Updated admin user view to show withdrawal status and provide controls
- Conditional buttons based on current withdrawal status

### 5. Security & UX Considerations
- Clear error messages for users when withdrawal is disabled
- Confirmation dialogs for admin actions
- Proper HTTP status codes (403 for forbidden access)
- Backward compatibility maintained (existing users can withdraw by default)

## How It Works

### For Users
1. When a user attempts to request a withdrawal, the system checks their `withdrawal_enabled` status
2. If `false`, the request is denied with a 403 error
3. If `true` (or column doesn't exist), the withdrawal request proceeds normally

### For Admins
1. Admins can view withdrawal status on the user details page
2. Admins can enable/disable withdrawal access with dedicated buttons
3. Confirmation prompts prevent accidental changes
4. Status is immediately reflected in the UI

## Testing
- Created comprehensive test command to verify functionality
- Verified that the `withdrawal_enabled` field is properly saved and retrieved
- Confirmed that the access control works as expected

## Files Modified
1. `app/Models/User.php` - Added field to `$fillable` and `hasWithdrawalAccess()` method
2. `app/Http/Controllers/API/TransactionController.php` - Added access check to withdrawal request
3. `app/Http/Controllers/Admin/UserController.php` - Added enable/disable methods
4. `routes/admin.php` - Added routes for withdrawal access control
5. `resources/views/admin/users/show.blade.php` - Updated UI with controls and status display
6. `database/migrations/2025_09_07_100000_add_withdrawal_enabled_to_users_table.php` - Database migration

## Usage

### Enabling Withdrawal Access (Admin)
```http
PUT /admin/users/{userId}/enable-withdrawal
```

### Disabling Withdrawal Access (Admin)
```http
PUT /admin/users/{userId}/disable-withdrawal
```

### Checking Withdrawal Access (Any context)
```php
if ($user->hasWithdrawalAccess()) {
    // User can withdraw
} else {
    // User cannot withdraw
}
```