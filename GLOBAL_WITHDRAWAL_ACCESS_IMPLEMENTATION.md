# Global Withdrawal Access Control Implementation

## Overview
This implementation changes the withdrawal access control from per-user to a global setting that affects all users in the VisionHub platform. When disabled, all users are prevented from making withdrawal requests.

## Features Implemented

### 1. Database Changes
- Removed `withdrawal_enabled` column from the `users` table
- Created `global_settings` table for storing application-wide settings
- Added seeder to create default global withdrawal setting

### 2. Global Setting Model
- Created `GlobalSetting` model for managing global settings
- Added helper methods for getting/setting values
- Implemented `isWithdrawalEnabled()` method for checking withdrawal status

### 3. User Model Update
- Removed `withdrawal_enabled` from `$fillable` array
- Updated `hasWithdrawalAccess()` method to check global setting instead of user-specific field

### 4. Admin Interface
- Added "Financial" tab to System Settings
- Created UI for enabling/disabling withdrawals globally
- Removed individual user withdrawal controls
- Added link to Financial Settings from user pages

### 5. Controller Updates
- Updated `SettingsController` to handle financial settings
- Modified `UserController` to work with global settings
- Added routes for global withdrawal control

### 6. Security & UX Considerations
- Clear visual indication of withdrawal status in user details
- Dedicated tab for financial settings
- Proper HTTP status codes (403 for forbidden access)
- Backward compatibility maintained (withdrawals enabled by default)

## How It Works

### For Users
1. When a user attempts to request a withdrawal, the system checks the global `withdrawal_enabled` setting
2. If `false`, the request is denied with a 403 error
3. If `true`, the withdrawal request proceeds normally

### For Admins
1. Admins can view and modify withdrawal status in the Financial Settings tab
2. Toggle switch and action buttons provide multiple ways to control withdrawals
3. Status is immediately reflected for all users

## Testing
- Created comprehensive test command to verify functionality
- Verified that the global setting properly affects all users
- Confirmed that the access control works as expected

## Files Modified
1. `app/Models/User.php` - Updated `hasWithdrawalAccess()` method and removed field from `$fillable`
2. `app/Models/GlobalSetting.php` - New model for global settings
3. `app/Http/Controllers/API/TransactionController.php` - No changes needed (uses User model method)
4. `app/Http/Controllers/Admin/UserController.php` - Updated methods to work with global settings
5. `app/Http/Controllers/Admin/SettingsController.php` - Added financial settings support
6. `routes/admin.php` - Added routes for global withdrawal control
7. `resources/views/admin/settings/index.blade.php` - Added Financial Settings tab
8. `resources/views/admin/users/show.blade.php` - Updated UI to reflect global setting
9. `database/migrations/2025_09_07_173945_remove_withdrawal_enabled_from_users_table.php` - Migration to remove user field
10. `database/migrations/2025_09_07_173955_create_global_settings_table.php` - Migration to create global settings table
11. `database/seeders/GlobalSettingsSeeder.php` - Seeder for default global settings

## Usage

### Checking Withdrawal Access (Any context)
```php
if ($user->hasWithdrawalAccess()) {
    // User can withdraw (global setting is enabled)
} else {
    // User cannot withdraw (global setting is disabled)
}
```

### Enabling Withdrawal Access (Admin)
```http
PUT /admin/settings/enable-withdrawal
```

### Disabling Withdrawal Access (Admin)
```http
PUT /admin/settings/disable-withdrawal
```

### Updating Financial Settings (Admin)
```http
PUT /admin/settings/financial
```