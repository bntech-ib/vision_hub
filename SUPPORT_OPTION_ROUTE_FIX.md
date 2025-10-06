# Support Option Route Parameter Fix

## Issue
"Missing required parameter for [Route: admin.support.update] [URI: admin/support/{support}] [Missing parameter: support]"

## Root Cause
The error occurred because the route parameter name didn't match what was being passed to the route generation function in the support options edit view.

## Solution
Changed the form action in `resources/views/admin/support/edit.blade.php` to explicitly pass the support option model:

**Before:**
```php
<form action="{{ route('admin.support.update', ['support' => $supportOption->id]) }}" method="POST" enctype="multipart/form-data">
```

**After:**
```php
<form action="{{ route('admin.support.update', $supportOption) }}" method="POST" enctype="multipart/form-data">
```

This explicitly tells Laravel to use the `id` of the `$supportOption` model for the `support` route parameter through route model binding.

## How It Works
1. When a user clicks "Edit" on a support option, they're taken to the edit page
2. The SupportController's `edit` method receives the SupportOption model via route model binding
3. The edit view displays the form with the correct route parameter binding
4. When the form is submitted, Laravel automatically resolves the SupportOption model from the route parameter
5. The SupportController's `update` method receives both the Request and the SupportOption model
6. The support option is updated successfully

## Verification
You can test that the route generation works correctly by running:
```bash
php artisan tinker
>>> route('admin.support.update', 1)
```

This should correctly produce a URL like `http://localhost:8000/admin/support/1`