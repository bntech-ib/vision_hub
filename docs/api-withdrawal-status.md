# API Withdrawal Status Endpoints

## Overview
This document describes how to access the withdrawal enabled status through the API endpoints.

## Available Endpoints

### 1. Get User Profile with Withdrawal Status
**Endpoint**: `GET /api/v1/user/profile`   
**Controller**: `API\UserProfileController@index`  
**Authentication**: Required (Sanctum token)

This endpoint returns the user's profile information including withdrawal status.

#### Response Example
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "country": "US",
      "has_bound_bank_account": true,
      "bank_account_bound_at": "2025-10-03T10:30:00.000000Z",
      "bank_account_holder_name": "John Doe",
      "bank_name": "Example Bank",
      "bank_branch": "Main Branch",
      "withdrawals_enabled": true,
      "can_request_withdrawal": true
    }
  }
}
```

### 2. Get Withdrawal Status Only
**Endpoint**: `GET /api/v1/user/withdrawal-status`  
**Controller**: `API\UserProfileController@getWithdrawalStatus`  
**Authentication**: Required (Sanctum token)

This endpoint returns only the withdrawal status information.

#### Response Example
```json
{
  "success": true,
  "data": {
    "withdrawals_enabled": true,
    "can_request_withdrawal": true,
    "user_withdrawal_enabled": true
  }
}
```

## Field Descriptions

### `withdrawals_enabled`
- **Type**: Boolean
- **Description**: Indicates if withdrawals are globally enabled by the admin
- **Source**: `App\Models\GlobalSetting::isWithdrawalEnabled()`

### `can_request_withdrawal`
- **Type**: Boolean
- **Description**: Indicates if the current user can request withdrawals (based on global setting)
- **Source**: `$user->hasWithdrawalAccess()`

### `user_withdrawal_enabled`
- **Type**: Boolean
- **Description**: Same as `can_request_withdrawal` - indicates if the user can request withdrawals
- **Source**: `$user->hasWithdrawalAccess()`

## Usage Examples

### JavaScript/Fetch API
```javascript
// Get user profile with withdrawal status
fetch('/api/v1/user/profile', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer YOUR_API_TOKEN',
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    const user = data.data.user;
    console.log('Withdrawals enabled:', user.withdrawals_enabled);
    console.log('Can request withdrawal:', user.can_request_withdrawal);
  }
});

// Get withdrawal status only
fetch('/api/v1/user/withdrawal-status', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer YOUR_API_TOKEN',
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log('Withdrawals enabled:', data.data.withdrawals_enabled);
    console.log('Can request withdrawal:', data.data.can_request_withdrawal);
  }
});
```

### Axios
```javascript
// Get user profile with withdrawal status
axios.get('/api/v1/user/profile', {
  headers: {
    'Authorization': 'Bearer YOUR_API_TOKEN'
  }
})
.then(response => {
  const user = response.data.data.user;
  console.log('Withdrawals enabled:', user.withdrawals_enabled);
  console.log('Can request withdrawal:', user.can_request_withdrawal);
});

// Get withdrawal status only
axios.get('/api/v1/user/withdrawal-status', {
  headers: {
    'Authorization': 'Bearer YOUR_API_TOKEN'
  }
})
.then(response => {
  const withdrawalStatus = response.data.data;
  console.log('Withdrawals enabled:', withdrawalStatus.withdrawals_enabled);
  console.log('Can request withdrawal:', withdrawalStatus.can_request_withdrawal);
});
```

## Implementation Details

### UserProfileController@index
The user profile endpoint was updated to include withdrawal status information:
```php
public function index(Request $request): JsonResponse
{
    $user = $request->user();
    
    return response()->json([
        'success' => true,
        'data' => [
            'user' => [
                // ... other user fields ...
                'withdrawals_enabled' => \App\Models\GlobalSetting::isWithdrawalEnabled(),
                'can_request_withdrawal' => $user->hasWithdrawalAccess(),
            ]
        ]
    ]);
}
```

### UserProfileController@getWithdrawalStatus
A new dedicated endpoint was added:
```php
public function getWithdrawalStatus(Request $request): JsonResponse
{
    $user = $request->user();
    
    return response()->json([
        'success' => true,
        'data' => [
            'withdrawals_enabled' => \App\Models\GlobalSetting::isWithdrawalEnabled(),
            'can_request_withdrawal' => $user->hasWithdrawalAccess(),
            'user_withdrawal_enabled' => $user->hasWithdrawalAccess()
        ]
    ]);
}
```

### User Model
The User model already had the `hasWithdrawalAccess()` method:
```php
/**
 * Check if user has withdrawal access enabled by admin
 */
public function hasWithdrawalAccess(): bool
{
    // Check global setting for withdrawal access
    return \App\Models\GlobalSetting::isWithdrawalEnabled();
}
```

### GlobalSetting Model
The GlobalSetting model provides the `isWithdrawalEnabled()` method:
```php
/**
 * Check if withdrawals are enabled globally
 *
 * @return bool
 */
public static function isWithdrawalEnabled(): bool
{
    $value = self::get('withdrawal_enabled', true);
    // Ensure we always return a boolean value
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}
```

## Error Handling

All endpoints require authentication. If called without a valid token, they will return a 401 Unauthorized response:

```json
{
  "message": "Unauthenticated."
}
```

## Testing

You can test these endpoints using tools like Postman or curl:

```bash
# Get user profile with withdrawal status
curl -X GET \
  http://your-domain.com/api/v1/user/profile \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"

# Get withdrawal status only
curl -X GET \
  http://your-domain.com/api/v1/user/withdrawal-status \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"
```

## Related Routes

Other withdrawal-related routes in the API:
- `POST /api/v1/wallet/withdraw` - Request a withdrawal
- `GET /api/v1/wallet/withdrawals` - Get user's withdrawal requests
- `POST /api/v1/wallet/withdrawals/{id}/cancel` - Cancel a withdrawal request