# Fix for "Undefined variable $stats in withdrawal page"

## Issue Description
The error "Undefined variable $stats" was occurring on the withdrawal index page (`resources/views/admin/withdrawals/index.blade.php`) because the view was trying to access a `$stats` variable that was not being passed from the controller.

## Root Cause
The `index` method in `app/Http/Controllers/Admin/WithdrawalController.php` was only passing the `$withdrawals` variable to the view but not the `$stats` variable that the view template was expecting.

## Solution
Updated the `index` method in the WithdrawalController to calculate and pass the required statistics data to the view:

```php
// Calculate stats
$stats = [
    'total_withdrawals' => WithdrawalRequest::count(),
    'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->count(),
    'approved_withdrawals' => WithdrawalRequest::where('status', 'approved')->count(),
    'total_amount_pending' => WithdrawalRequest::where('status', 'pending')->sum('amount'),
    'total_amount_approved' => WithdrawalRequest::where('status', 'approved')->sum('amount'),
];

return view('admin.withdrawals.index', compact('withdrawals', 'stats'));
```

## Files Modified
1. `app/Http/Controllers/Admin/WithdrawalController.php` - Updated the `index` method to calculate and pass statistics data
2. Added filter handling for amount_min, amount_max, date_from, and date_to parameters

## Statistics Provided
The fix provides the following statistics to the withdrawal index page:
- `total_withdrawals`: Total number of withdrawal requests
- `pending_withdrawals`: Number of pending withdrawal requests
- `approved_withdrawals`: Number of approved withdrawal requests
- `total_amount_pending`: Total amount of pending withdrawals
- `total_amount_approved`: Total amount of approved withdrawals

## Verification
The fix ensures that the `$stats` variable is properly defined and passed to the view, resolving the "Undefined variable $stats" error.