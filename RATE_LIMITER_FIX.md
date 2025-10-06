# Rate Limiter Fix

## Issue
"Rate limiter [auth] is not defined" error was occurring when trying to access authentication routes that use the `throttle:auth` middleware.

## Root Cause
The rate limiter definitions in the bootstrap/app.php file were not being properly registered or recognized by the application, likely due to caching or loading order issues.

## Solution
Created a dedicated RateLimiterServiceProvider to properly register the rate limiters and ensure they are available throughout the application lifecycle.

## Changes Made

### 1. Created RateLimiterServiceProvider
- Created `app/Providers/RateLimiterServiceProvider.php`
- Moved rate limiter definitions from bootstrap/app.php to the service provider
- Registered the service provider in bootstrap/app.php

### 2. Updated bootstrap/app.php
- Removed inline rate limiter definitions
- Added RateLimiterServiceProvider to the providers list

## Rate Limiters Defined

1. **auth** - 5 requests per minute (for authentication routes)
2. **api** - 60 requests per minute (for general API routes)
3. **uploads** - 10 requests per minute (for file upload routes)

## How It Works
1. The RateLimiterServiceProvider is loaded during application bootstrapping
2. Rate limiters are registered with the RateLimiter facade
3. Routes can reference these limiters using the throttle middleware (e.g., `throttle:auth`)

## Testing
After implementing this fix:
1. Clear all caches: `php artisan config:clear`, `php artisan route:clear`, `php artisan cache:clear`
2. Test authentication routes to ensure they're properly rate limited
3. Verify that the "Rate limiter [auth] is not defined" error no longer occurs

## Benefits
- Centralized rate limiter configuration in a dedicated service provider
- Proper registration and availability of rate limiters
- Follows Laravel best practices for service provider organization
- Eliminates the error and restores proper rate limiting functionality