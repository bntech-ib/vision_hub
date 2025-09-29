# Fix Summary: Resolving "Cannot redeclare App\Http\Controllers\Admin\UserController::enableWithdrawal()" Error

## Problem
The error "Cannot redeclare App\Http\Controllers\Admin\UserController::enableWithdrawal()" occurred because there were two methods with the same name in the UserController:
1. `enableWithdrawal(User $user)` - for enabling withdrawal access for a specific user
2. `enableWithdrawal()` - for enabling withdrawal access globally

The same issue existed for `disableWithdrawal()` methods.

## Solution
1. **Renamed the global methods** in `app/Http/Controllers/Admin/UserController.php`:
   - `enableWithdrawal()` → `enableWithdrawalGlobally()`
   - `disableWithdrawal()` → `disableWithdrawalGlobally()`

2. **Updated the routes** in `routes/admin.php` to use the new method names:
   - `UserController::enableWithdrawal` → `UserController::enableWithdrawalGlobally`
   - `UserController::disableWithdrawal` → `UserController::disableWithdrawalGlobally`

3. **Cleared caches** to ensure the changes take effect:
   - Route cache cleared
   - View cache cleared

## Files Modified
1. `app/Http/Controllers/Admin/UserController.php` - Renamed conflicting methods
2. `routes/admin.php` - Updated route definitions to use new method names

## Verification
- ✅ Syntax check passed with no errors
- ✅ Route list shows correct method names
- ✅ No more "Cannot redeclare" error

The fix maintains all existing functionality while resolving the method name conflict.