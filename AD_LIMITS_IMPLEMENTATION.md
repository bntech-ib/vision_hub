# Ad and Brain Teaser Limits Implementation

## Overview
This document describes the implementation of ad and brain teaser limits based on user packages, as well as preventing users from seeing content they've already interacted with.

## Changes Made

### 1. User Model Enhancements
Added the following methods to the User model (`app/Models/User.php`):

- `hasReachedAdViewLimit()`: Checks if a user has reached their monthly ad view limit based on their package
- `hasBrainTeaserAccess()`: Checks if a user's package includes brain teaser access
- `getAvailableAdsQuery()`: Returns a query for ads available to the user, excluding those they've already viewed
- `getAvailableBrainTeasersQuery()`: Returns a query for brain teasers available to the user, excluding those they've already attempted

### 2. AdController Updates
Modified the AdController (`app/Http/Controllers/API/AdController.php`) to:

- Check if users have reached their ad view limit before showing ads
- Filter ads to only show those the user hasn't viewed yet
- Prevent users from interacting with ads if they've reached their limit

### 3. BrainTeaserController Updates
Modified the BrainTeaserController (`app/Http/Controllers/API/BrainTeaserController.php`) to:

- Check if users have brain teaser access based on their package before showing brain teasers
- Filter brain teasers to only show those the user hasn't attempted yet
- Prevent users from submitting answers if they don't have access

## Implementation Details

### Ad Limits
- Users with packages that have `ad_views_limit` set to 0 or null have unlimited ad views
- Users with a positive `ad_views_limit` are limited to that many ad views per month
- Ad views are counted by looking at `AdInteraction` records of type 'view' for the current month
- Ads that users have already viewed are excluded from their feed

### Brain Teaser Limits
- Users can only access brain teasers if their package has `brain_teaser_access` set to true
- Brain teasers that users have already attempted are excluded from their feed
- Users cannot submit answers to brain teasers they've already attempted

## API Endpoints Affected

### Ad Endpoints
- `GET /api/v1/ads`: Now checks ad view limits and filters out viewed ads
- `POST /api/v1/ads/{id}/interact`: Now checks ad view limits before recording interactions

### Brain Teaser Endpoints
- `GET /api/v1/brain-teasers`: Now checks package access and filters out attempted brain teasers
- `GET /api/v1/brain-teasers/{id}`: Now checks package access
- `GET /api/v1/brain-teasers/daily`: Now checks package access
- `POST /api/v1/brain-teasers/{id}/submit`: Now checks package access and prevents duplicate attempts

## Error Responses

### Ad Limits
When users reach their ad view limit, they receive a 403 response:
```json
{
  "success": false,
  "message": "You have reached your ad view limit for this month based on your package."
}
```

### Brain Teaser Access
When users don't have brain teaser access, they receive a 403 response:
```json
{
  "success": false,
  "message": "Your current package does not include access to brain teasers."
}
```

## Package Configuration

Packages can be configured with the following fields:
- `ad_views_limit`: Integer representing the maximum number of ad views per month (0 = unlimited)
- `brain_teaser_access`: Boolean indicating whether the package includes brain teaser access

## Testing

To test these features:

1. Create a user with a package that has limited ad views
2. Have the user view ads until they reach their limit
3. Verify they can no longer see or interact with ads
4. Create a user with a package without brain teaser access
5. Verify they cannot access brain teasers
6. Create a user with a package with brain teaser access
7. Have them attempt a brain teaser
8. Verify they cannot attempt the same brain teaser again