# Support Options Rate Limiter Fix

## Issue
Potential rate limiter issues with support options routes due to missing ApiController class and route configuration problems.

## Root Cause
1. Missing ApiController class that was referenced in the routes
2. Route configuration issues that could cause rate limiter problems

## Solution
1. Created missing ApiController class
2. Ensured support options routes are properly configured with rate limiters
3. Verified that the auth rate limiter is properly defined and available

## Changes Made

### 1. Created ApiController
- Created `app/Http/Controllers/ApiController.php`
- Added methods for info, health, fileTypes, and maintenance endpoints

### 2. Verified Route Configuration
- Confirmed that support options routes are properly protected by the auth rate limiter
- Verified that the rate limiter definitions are correctly set up in the RateLimiterServiceProvider

## Support Options Routes

### Public Routes
- `GET /api/v1/support-options` - Public index of support options (no rate limiting)

### Authenticated Routes (Protected by auth rate limiter)
- `GET /api/v1/support-options` - Index of support options
- `POST /api/v1/support-options` - Create new support option
- `GET /api/v1/support-options/{support_option}` - Show specific support option
- `PUT /api/v1/support-options/{support_option}` - Update specific support option
- `DELETE /api/v1/support-options/{support_option}` - Delete specific support option

## Rate Limiters
1. **auth** - 5 requests per minute (for authentication routes including support options)
2. **api** - 60 requests per minute (for general API routes)
3. **uploads** - 10 requests per minute (for file upload routes)

## Testing
After implementing this fix:
1. Clear all caches: `php artisan config:clear`, `php artisan route:clear`, `php artisan cache:clear`
2. Test support options routes to ensure they're properly rate limited
3. Verify that no "Rate limiter is not defined" errors occur

## Benefits
- Fixed missing ApiController dependency
- Ensured proper rate limiting for support options routes
- Maintained security by keeping authenticated routes protected
- Provided public access to support options without rate limiting