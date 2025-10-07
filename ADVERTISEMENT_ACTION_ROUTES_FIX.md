# Fix for "POST method is not supported for route admin/ads/1/approve"

## Issue Description
The error "The POST method is not supported for route admin/ads/1/approve. Supported methods: PUT." was occurring when trying to approve/reject/pause advertisements from the admin interface.

## Root Cause
The issue was a mismatch between the HTTP methods used in the view forms and the route definitions:

1. **Route Definitions** (in `routes/admin.php`):
   - `Route::put('ads/{ad}/approve', [AdController::class, 'approve'])->name('ads.approve');`
   - `Route::put('ads/{ad}/reject', [AdController::class, 'reject'])->name('ads.reject');`
   - `Route::put('ads/{ad}/pause', [AdController::class, 'pause'])->name('ads.pause');`

2. **View Forms** (in `resources/views/admin/ads/show.blade.php`):
   - All forms were using `method="POST"` but without the `@method('PUT')` directive

## Solution
Updated the view forms in `resources/views/admin/ads/show.blade.php` to include the `@method('PUT')` directive for all action forms:

```php
<!-- Before -->
<form action="{{ route('admin.ads.approve', $ad) }}" method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-success btn-sm w-100 mb-2">Approve</button>
</form>

<!-- After -->
<form action="{{ route('admin.ads.approve', $ad) }}" method="POST" class="d-inline">
    @csrf
    @method('PUT')
    <button type="submit" class="btn btn-success btn-sm w-100 mb-2">Approve</button>
</form>
```

## Files Modified
1. `resources/views/admin/ads/show.blade.php` - Added `@method('PUT')` directive to all action forms:
   - Approve form (for pending ads)
   - Reject form (for pending ads)
   - Pause form (for active ads)
   - Resume form (for paused ads)

## Verification
The fix ensures that the HTTP methods used in the view forms match the route definitions, resolving the "POST method is not supported" error.