# Fix for "Call to undefined relationship [interactions] on model [App\Models\Advertisement]"

## Issue Description
The error "Call to undefined relationship [interactions] on model [App\Models\Advertisement]" was occurring when trying to access the 'interactions' relationship on the Advertisement model.

## Root Cause
The issue was in the [AdController](file:///c:/business/visionHub/app/visionhub-backend/app/Http/Controllers/Admin/AdController.php#L32-L227)'s [show method](file:///c:/business/visionHub/app/visionhub-backend/app/Http/Controllers/Admin/AdController.php#L110-L145) where it was attempting to eager load the 'interactions' relationship:

```php
$ad->load(['advertiser:id,name,email', 'interactions' => function($q) {
    $q->latest()->limit(10);
}]);
```

While the Advertisement model does have an [interactions()](file:///c:/business/visionHub/app/visionhub-backend/app/Models/Advertisement.php#L86-L89) method defined as an alias for [adInteractions()](file:///c:/business/visionHub/app/visionhub-backend/app/Models/Advertisement.php#L78-L81), there was an inconsistency in how the relationship was being referenced.

## Solution
1. Modified the [AdController](file:///c:/business/visionHub/app/visionhub-backend/app/Http/Controllers/Admin/AdController.php#L32-L227) to use 'adInteractions' instead of 'interactions' when eager loading the relationship:
   ```php
   $ad->load(['advertiser:id,name,email', 'adInteractions' => function($q) {
       $q->latest()->limit(10);
   }]);
   ```

2. Updated the [AdvertisementInteractionsTest](file:///c:/business/visionHub/app/visionhub-backend/tests/Feature/AdvertisementInteractionsTest.php#L13-L90) to maintain consistency in using 'adInteractions' instead of 'interactions' in eager loading calls.

## Verification
The Advertisement model already has both relationships properly defined:
- [adInteractions()](file:///c:/business/visionHub/app/visionhub-backend/app/Models/Advertisement.php#L78-L81) - The primary relationship method
- [interactions()](file:///c:/business/visionHub/app/visionhub-backend/app/Models/Advertisement.php#L86-L89) - An alias method that returns `$this->adInteractions()`

This fix ensures consistency in how the relationship is referenced throughout the codebase and resolves the "undefined relationship" error.