# Ad Earning System Documentation

## Overview

The ad earning system allows users to earn rewards by interacting with advertisements. Users can earn rewards for both viewing and clicking ads, with click rewards being twice the view reward.

## How It Works

### 1. Reward Calculation

The reward amount is calculated based on the user's package settings:

```
View Reward = daily_earning_limit / ad_limits
Click Reward = 2 × View Reward
```

**Example:**
- Package daily_earning_limit: $10.00
- Package ad_limits: 20 interactions per day
- View Reward: $10.00 / 20 = $0.50 per view
- Click Reward: 2 × $0.50 = $1.00 per click

### 2. Daily Limits

Users are limited by their package's `ad_limits` setting. Once a user reaches their daily limit, they cannot earn more rewards from ad interactions until the next day.

### 3. Earning Process

1. User interacts with an ad (view or click)
2. System validates the ad is active
3. System checks if user has reached daily limit
4. System calculates reward based on package
5. Reward is added to user's wallet balance
6. Transaction record is created
7. Ad interaction is recorded

## API Endpoints

### Record Ad Interaction

```
POST /api/v1/ads/{ad_id}/interact
```

**Request Body:**
```json
{
  "type": "view"  // or "click"
}
```

**Response (Success):**
```json
{
  "success": true,
  "data": {
    "interaction": {
      "id": "1",
      "user_id": "1",
      "advertisement_id": "1",
      "type": "view",
      "reward_earned": 0.5,
      "interacted_at": "2025-01-01T10:00:00.000000Z"
    },
    "remaining_interactions": 19
  },
  "message": "Ad view recorded successfully"
}
```

### Get Ad Statistics

```
GET /api/v1/ads/stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "today_views": 5,
    "today_clicks": 2,
    "daily_limit": 20,
    "remaining_interactions": 13,
    "has_reached_limit": false
  },
  "message": "Ad statistics retrieved successfully"
}
```

### Get Ad Interaction History

```
GET /api/v1/ads/history/my-interactions
```

**Response:**
```json
{
  "success": true,
  "data": {
    "interactions": [
      {
        "id": "1",
        "advertisement_id": "1",
        "type": "view",
        "reward_earned": 0.5,
        "interacted_at": "2025-01-01T10:00:00.000000Z",
        "advertisement": {
          "id": "1",
          "title": "Test Ad",
          "description": "Test advertisement"
        }
      }
    ],
    "meta": {
      "pagination": {
        "total": 1,
        "count": 1,
        "per_page": 15,
        "current_page": 1,
        "total_pages": 1
      }
    }
  },
  "message": "Ad interactions retrieved successfully"
}
```

## Package Fields

The following package fields control the ad earning system:

| Field | Description | Default |
|-------|-------------|---------|
| `daily_earning_limit` | Maximum earnings per day | 0.00 |
| `ad_limits` | Maximum ad interactions per day | 0 |

## Database Tables

### ad_interactions

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `user_id` | bigint | Foreign key to users table |
| `advertisement_id` | bigint | Foreign key to advertisements table |
| `type` | enum | 'view' or 'click' |
| `reward_earned` | decimal | Amount earned from interaction |
| `metadata` | json | Additional interaction data |
| `interacted_at` | timestamp | When interaction occurred |

### transactions

When a user earns from ad interactions, a transaction record is created:

| Column | Value |
|--------|-------|
| `user_id` | User ID |
| `amount` | Reward amount |
| `type` | 'earning' |
| `description` | "Reward for viewing/clicking advertisement #{id}" |
| `status` | 'completed' |

## Testing

Run the feature tests to verify the earning system works correctly:

```bash
php artisan test --filter=AdEarningTest
```

## Implementation Details

### Key Methods

1. **UserPackage::calculateEarningPerAd()** - Calculates reward per ad interaction
2. **User::hasReachedDailyAdInteractionLimit()** - Checks if user reached daily limit
3. **User::getRemainingDailyAdInteractions()** - Gets remaining interactions for today
4. **User::addToWallet()** - Adds earnings to user's wallet balance

### Earning Flow

1. User makes POST request to `/api/v1/ads/{id}/interact`
2. AdController::interact() method handles the request
3. Validates ad is active and user hasn't reached limit
4. Calculates reward using package settings
5. Records interaction in ad_interactions table
6. Updates ad statistics (impressions/clicks)
7. Adds reward to user's wallet
8. Creates transaction record
9. Returns success response with interaction details

## Error Handling

The system handles the following error cases:

1. **Invalid ad ID** - Returns 404 Not Found
2. **Inactive ad** - Returns 400 Bad Request
3. **Daily limit reached** - Returns 403 Forbidden
4. **Database errors** - Returns 500 Internal Server Error with transaction rollback

## Security

- All endpoints require authentication via Sanctum tokens
- Rate limiting is applied to API endpoints
- Database transactions ensure data consistency
- Input validation prevents malicious data