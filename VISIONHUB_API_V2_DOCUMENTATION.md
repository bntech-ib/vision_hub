# VisionHub API Documentation V2

## Overview
This document provides comprehensive documentation for the VisionHub API endpoints, including request/response formats, error handling, and implementation details.

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
Most API endpoints require authentication using Bearer tokens. Include the token in the Authorization header:
```
Authorization: Bearer <your-token>
```

## Response Format
All API responses follow a consistent format:

### Success Response
```json
{
  "success": true,
  "data": {
    // Endpoint-specific data
  },
  "message": "Descriptive success message"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error information" // Optional
}
```

## Public Endpoints

### Get API Information
**GET** `/info`

Retrieve information about the API.

**Response:**
```json
{
  "success": true,
  "data": {
    "name": "VisionHub API",
    "version": "1.0",
    "description": "API for VisionHub platform"
  },
  "message": "API information retrieved successfully"
}
```

### Get Health Status
**GET** `/health`

Retrieve the health status of the system.

**Response:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "database": "connected",
    "cache": "available"
  },
  "message": "System is healthy"
}
```

### Get Supported File Types
**GET** `/file-types`

Retrieve the list of supported file types.

**Response:**
```json
{
  "success": true,
  "data": {
    "fileTypes": [
      "jpg",
      "jpeg",
      "png",
      "gif",
      "bmp",
      "tiff",
      "webp"
    ]
  },
  "message": "File types retrieved successfully"
}
```

### Get Maintenance Status
**GET** `/maintenance`

Check if the system is currently under maintenance.

**Response:**
```json
{
  "success": true,
  "data": {
    "is_maintenance": false,
    "message": "System is operational",
    "timestamp": "2025-09-04T10:00:00.000000Z"
  },
  "message": "Maintenance status retrieved successfully"
}
```

### User Registration
**POST** `/auth/register`

Register a new user account.

**Request Body:**
```json
{
  "fullName": "string",
  "username": "string",
  "email": "string",
  "password": "string",
  "confirmPassword": "string",
  "accessKey": "string",
  "country": "string (optional)",
  "phone": "string (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "username": "johndoe",
      "email": "john@example.com",
      "fullName": "John Doe",
      "phone": "+1234567890",
      "country": "USA",
      "package": {
        "id": "1",
        "name": "Basic Plan",
        "price": 9.99,
        "benefits": [
          "Up to 10 projects",
          "Basic analytics",
          "Email support"
        ],
        "duration": 30
      },
      "referralCode": "ABC123",
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "updatedAt": "2025-09-04T10:00:00.000000Z"
    },
    "token": "1|abcdefghijk1234567890"
  },
  "message": "Registration successful! Welcome to VisionHub!"
}
```

**Error Responses:**
- 422: Validation errors (email already taken, invalid access key, etc.)

### User Login
**POST** `/auth/login`

Authenticate a user and obtain an access token.

**Request Body:**
```json
{
  "email": "string",
  "password": "string"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "username": "johndoe",
      "email": "john@example.com",
      "fullName": "John Doe",
      "phone": "+1234567890",
      "country": "USA",
      "package": {
        "id": "1",
        "name": "Basic Plan",
        "price": 9.99,
        "benefits": [
          "Up to 10 projects",
          "Basic analytics",
          "Email support"
        ],
        "duration": 30
      },
      "referralCode": "ABC123",
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "updatedAt": "2025-09-04T10:00:00.000000Z"
    },
    "token": "1|abcdefghijk1234567890"
  },
  "message": "Login successful"
}
```

**Error Responses:**
- 422: Invalid credentials

## Protected Endpoints

### Get Authenticated User
**GET** `/auth/user`

Retrieve details of the authenticated user.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "username": "johndoe",
      "email": "john@example.com",
      "fullName": "John Doe",
      "phone": "+1234567890",
      "country": "USA",
      "package": {
        "id": "1",
        "name": "Basic Plan",
        "price": 9.99,
        "benefits": [
          "Up to 10 projects",
          "Basic analytics",
          "Email support"
        ],
        "duration": 30
      },
      "referralCode": "ABC123",
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "updatedAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "User details retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Logout
**POST** `/auth/logout`

Logout the current user session.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Logout All Sessions
**POST** `/auth/logout-all`

Logout all user sessions.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "message": "Logged out from all sessions successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Update Profile
**PUT** `/auth/profile`

Update the authenticated user's profile information.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "fullName": "string",
  "username": "string",
  "email": "string",
  "phone": "string (optional)",
  "country": "string (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "username": "johndoe",
      "email": "john@example.com",
      "fullName": "John Doe",
      "phone": "+1234567890",
      "country": "USA",
      "package": {
        "id": "1",
        "name": "Basic Plan",
        "price": 9.99,
        "benefits": [
          "Up to 10 projects",
          "Basic analytics",
          "Email support"
        ],
        "duration": 30
      },
      "referralCode": "ABC123",
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "updatedAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Profile updated successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 422: Validation errors

### Change Password
**PUT** `/auth/change-password`

Change the authenticated user's password.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "currentPassword": "string",
  "newPassword": "string",
  "confirmPassword": "string"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 422: Invalid current password

### Get API Tokens
**GET** `/auth/tokens`

Retrieve the authenticated user's API tokens.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "tokens": [
      {
        "id": "1",
        "name": "auth-token",
        "abilities": [
          "*"
        ],
        "lastUsedAt": "2025-09-04T10:00:00.000000Z",
        "createdAt": "2025-09-04T10:00:00.000000Z"
      }
    ]
  },
  "message": "Tokens retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Create API Token
**POST** `/auth/tokens`

Create a new API token for the authenticated user.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "name": "string",
  "abilities": "array (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "token": "1|abcdefghijk1234567890"
  },
  "message": "Token created successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 422: Validation errors

### Revoke API Token
**DELETE** `/auth/tokens`

Revoke the current API token.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "message": "Token revoked successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Projects
**GET** `/projects`

Retrieve all projects for the authenticated user.

**Headers:**
```
Authorization: Bearer <token>
```

**Query Parameters:**
- `page`: Page number (optional)
- `limit`: Number of items per page (optional)

**Success Response:**
```json
{
  "success": true,
  "data": {
    "projects": [
      {
        "id": "1",
        "name": "My First Project",
        "description": "Description of my first project",
        "status": "active",
        "settings": {
          "autoTag": true,
          "quality": "high"
        },
        "userId": "1",
        "createdAt": "2025-09-04T10:00:00.000000Z",
        "updatedAt": "2025-09-04T10:00:00.000000Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "lastPage": 1,
      "perPage": 10,
      "total": 1
    }
  },
  "message": "Projects retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Create Project
**POST** `/projects`

Create a new project.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "name": "string",
  "description": "string",
  "status": "string (optional)",
  "settings": "object (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "project": {
      "id": "1",
      "name": "My First Project",
      "description": "Description of my first project",
      "status": "active",
      "settings": {
        "autoTag": true,
        "quality": "high"
      },
      "userId": "1",
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "updatedAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Project created successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 422: Validation errors

### Get Specific Project
**GET** `/projects/{id}`

Retrieve a specific project by ID.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "project": {
      "id": "1",
      "name": "My First Project",
      "description": "Description of my first project",
      "status": "active",
      "settings": {
        "autoTag": true,
        "quality": "high"
      },
      "userId": "1",
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "updatedAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Project retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Project not found

### Update Project
**PUT** `/projects/{id}`

Update a specific project by ID.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "name": "string",
  "description": "string",
  "status": "string (optional)",
  "settings": "object (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "project": {
      "id": "1",
      "name": "My Updated Project",
      "description": "Updated description of my project",
      "status": "active",
      "settings": {
        "autoTag": true,
        "quality": "high"
      },
      "userId": "1",
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "updatedAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Project updated successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Project not found
- 422: Validation errors

### Delete Project
**DELETE** `/projects/{id}`

Delete a specific project by ID.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "message": "Project deleted successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Project not found

### Get Brain Teasers
**GET** `/brain-teasers`

Retrieve available brain teasers for the user.

**Headers:**
```
Authorization: Bearer <token>
```

**Query Parameters:**
- `limit`: Number of items to retrieve (optional, default: 10)

**Success Response:**
```json
{
  "success": true,
  "data": {
    "brainTeasers": [
      {
        "id": "1",
        "title": "Math Problem",
        "question": "What is 2+2?",
        "options": [
          "1",
          "2",
          "3",
          "4"
        ],
        "category": "Math",
        "difficulty": "easy",
        "rewardAmount": 10,
        "startDate": "2025-09-04T00:00:00.000000Z",
        "endDate": "2025-09-10T00:00:00.000000Z",
        "status": "active",
        "createdAt": "2025-09-04T10:00:00.000000Z",
        "userAttempt": {
          "attempted": false
        }
      }
    ]
  },
  "message": "Brain teasers retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 500: Internal server error

### Get Specific Brain Teaser
**GET** `/brain-teasers/{id}`

Retrieve a specific brain teaser by ID.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "brainTeaser": {
      "id": "1",
      "title": "Math Problem",
      "question": "What is 2+2?",
      "options": [
        "1",
        "2",
        "3",
        "4"
      ],
      "category": "Math",
      "difficulty": "easy",
      "rewardAmount": 10,
      "startDate": "2025-09-04T00:00:00.000000Z",
      "endDate": "2025-09-10T00:00:00.000000Z",
      "status": "active",
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "userAttempt": {
        "attempted": false
      }
    }
  },
  "message": "Brain teaser retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Brain teaser not found or not active
- 500: Internal server error

### Submit Brain Teaser Answer
**POST** `/brain-teasers/{id}/submit`

Submit an answer for a brain teaser.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "answer": "string or numeric value (will be converted to string)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "attempt": {
      "id": "1",
      "brainTeaserId": "1",
      "answer": "4",
      "isCorrect": true,
      "attemptedAt": "2025-09-04T10:00:00.000000Z",
      "rewardEarned": 10,
      "correctAnswer": "4",
      "explanation": "2+2 equals 4"
    }
  },
  "message": "Correct answer! Reward earned."
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Brain teaser not found or not active
- 400: Already attempted
- 422: Validation errors

### Attempt Brain Teaser Answer (Alias)
**POST** `/brain-teasers/{id}/attempt`

Alias endpoint for submitting an answer for a brain teaser. This endpoint functions identically to `/brain-teasers/{id}/submit`.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "answer": "string or numeric value (will be converted to string)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "attempt": {
      "id": "1",
      "brainTeaserId": "1",
      "answer": "4",
      "isCorrect": true,
      "attemptedAt": "2025-09-04T10:00:00.000000Z",
      "rewardEarned": 10,
      "correctAnswer": "4",
      "explanation": "2+2 equals 4"
    }
  },
  "message": "Correct answer! Reward earned."
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Brain teaser not found or not active
- 400: Already attempted
- 422: Validation errors

### Get Brain Teaser History
**GET** `/brain-teasers/my-attempts`

Retrieve the user's brain teaser attempt history.

**Headers:**
```
Authorization: Bearer <token>
```

**Query Parameters:**
- `limit`: Number of items to retrieve (optional, default: 10)

**Success Response:**
```json
{
  "success": true,
  "data": {
    "attempts": [
      {
        "id": "1",
        "brainTeaser": {
          "id": "1",
          "title": "Math Problem",
          "category": "Math",
          "difficulty": "easy"
        },
        "answer": "4",
        "isCorrect": true,
        "attemptedAt": "2025-09-04T10:00:00.000000Z",
        "rewardEarned": 10
      }
    ]
  },
  "message": "Brain teaser history retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 500: Internal server error

### Get Brain Teaser Statistics
**GET** `/brain-teasers/my-stats`

Retrieve the user's brain teaser statistics.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "stats": {
      "totalAttempts": 5,
      "correctAttempts": 4,
      "accuracy": 80,
      "totalAvailable": 10,
      "completed": 5,
      "completionRate": 50,
      "totalRewardsEarned": 40
    }
  },
  "message": "Brain teaser statistics retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 500: Internal server error

### Get Advertisements
**GET** `/ads`

Retrieve available advertisements.

**Headers:**
```
Authorization: Bearer <token>
```

**Query Parameters:**
- `limit`: Number of items to retrieve (optional, default: 10)
- `page`: Page number (optional, default: 1)
- `category`: Filter by category (optional)
- `search`: Search term (optional)

**Success Response:**
```json
{
  "success": true,
  "data": {
    "ads": [
      {
        "id": "1",
        "title": "Summer Sale",
        "description": "50% off on all products",
        "imageUrl": "https://example.com/ad.jpg",
        "targetUrl": "https://example.com/sale",
        "type": "banner",
        "rewardAmount": 0.5,
        "startDate": "2025-09-01T00:00:00.000000Z",
        "endDate": "2025-09-30T00:00:00.000000Z",
        "status": "active",
        "createdAt": "2025-09-01T10:00:00.000000Z"
      }
    ]
  },
  "message": "Advertisements retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 403: Daily ad interaction limit reached

### Get Specific Advertisement
**GET** `/ads/{id}`

Retrieve a specific advertisement by ID.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "ad": {
      "id": "1",
      "title": "Summer Sale",
      "description": "50% off on all products",
      "imageUrl": "https://example.com/ad.jpg",
      "targetUrl": "https://example.com/sale",
      "type": "banner",
      "rewardAmount": 0.5,
      "startDate": "2025-09-01T00:00:00.000000Z",
      "endDate": "2025-09-30T00:00:00.000000Z",
      "status": "active",
      "createdAt": "2025-09-01T10:00:00.000000Z"
    }
  },
  "message": "Advertisement retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Advertisement not found

### Interact with Advertisement
**POST** `/ads/{id}/interact`

Record an interaction with an advertisement.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "type": "string (view|click)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "interaction": {
      "id": "1",
      "adId": "1",
      "userId": "1",
      "type": "view",
      "rewardEarned": 0.5,
      "createdAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Advertisement interaction recorded successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Advertisement not found
- 422: Validation errors
- 400: Advertisement not active
- 403: Daily ad interaction limit reached

### Get Advertisement Statistics
**GET** `/ads/stats`

Retrieve user's advertisement interaction statistics.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "today_views": 5,
    "today_clicks": 2,
    "daily_limit": 50,
    "remaining_interactions": 43,
    "has_reached_limit": false
  },
  "message": "Ad statistics retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get My Ad Interactions
**GET** `/ads/history/my-interactions`

Retrieve user's advertisement interaction history.

**Headers:**
```
Authorization: Bearer <token>
```

**Query Parameters:**
- `limit`: Number of items to retrieve (optional, default: 15)
- `page`: Page number (optional, default: 1)

**Success Response:**
```json
{
  "success": true,
  "data": {
    "interactions": [
      {
        "id": 1,
        "advertisement_id": 5,
        "type": "view",
        "reward_earned": 2.50,
        "interacted_at": "2025-09-04T10:00:00.000000Z",
        "advertisement": {
          "id": 5,
          "title": "Summer Sale",
          "description": "50% off on all products"
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

**Error Responses:**
- 401: Unauthenticated

### Get Transactions
**GET** `/transactions`

Retrieve the user's transactions.

**Headers:**
```
Authorization: Bearer <token>
```

**Query Parameters:**
- `type`: Filter by transaction type (earning, purchase, withdrawal) (optional)
- `status`: Filter by status (completed, pending, failed) (optional)
- `date_from`: Filter by date from (YYYY-MM-DD) (optional)
- `date_to`: Filter by date to (YYYY-MM-DD) (optional)
- `reference_type`: Filter by reference type (optional)
- `limit`: Number of items to retrieve (optional, default: 15)
- `page`: Page number (optional, default: 1)

**Success Response:**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": "1",
        "userId": "1",
        "type": "earning",
        "amount": 10.5,
        "currency": "NGN",
        "description": "Advertisement view reward",
        "status": "completed",
        "referenceType": "App\\Models\\Advertisement",
        "referenceId": "5",
        "createdAt": "2025-09-04T10:00:00.000000Z",
        "updatedAt": "2025-09-04T10:00:00.000000Z"
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
  "message": "Transactions retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Transaction Details
**GET** `/transactions/{id}`

Retrieve details of a specific transaction.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "transaction": {
      "id": "1",
      "userId": "1",
      "type": "earning",
      "amount": 10.5,
      "currency": "NGN",
      "description": "Advertisement view reward",
      "status": "completed",
      "referenceType": "App\\Models\\Advertisement",
      "referenceId": "5",
      "metadata": null,
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "updatedAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Transaction retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Transaction not found

### Get Wallet Summary
**GET** `/wallet/summary`

Retrieve the user's wallet summary.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "currentBalance": 100,
    "availableBalance": 90,
    "pendingWithdrawals": 10,
    "lifetimeStats": {
      "totalEarnings": 150,
      "totalSpending": 0,
      "totalWithdrawals": 50,
      "netBalance": 100
    },
    "thisMonthStats": {
      "earnings": 100,
      "spending": 0,
      "net": 100
    },
    "earningsBreakdown": [
      {
        "source": "Advertisement Views",
        "totalAmount": 100,
        "transactionCount": 20
      }
    ],
    "recentTransactions": [
      {
        "id": "1",
        "type": "earning",
        "amount": 10.5,
        "description": "Advertisement view reward",
        "createdAt": "2025-09-04T10:00:00.000000Z"
      }
    ]
  },
  "message": "Wallet summary retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Request Withdrawal
**POST** `/wallet/withdraw`

Request a withdrawal from the user's wallet.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "amount": "number",
  "payment_method_id": "integer (1 for wallet balance, 2 for referral earnings)"
}
```

**Note:** Users must have previously bound their bank account details to their profile before requesting a withdrawal. The system will use the stored bank account information for processing the withdrawal.

**Success Response:**
```json
{
  "success": true,
  "data": {
    "withdrawal": {
      "id": "1",
      "userId": "1",
      "amount": 50.0,
      "currency": "NGN",
      "paymentMethod": {
        "id": 1,
        "name": "Wallet Balance"
      },
      "accountDetails": {
        "accountName": "John Doe",
        "accountNumber": "1234567890",
        "bankName": "Bank of America"
      },
      "status": "pending",
      "requestedAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Withdrawal request submitted successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 422: Validation errors (including invalid payment_method_id)
- 400: Insufficient balance or bank account not bound
- 403: Withdrawal access disabled

### Get Referral Statistics
**GET** `/dashboard/referral-stats`

Get detailed referral statistics including user information.

**Note:** As of the latest update, referral earnings are only awarded for direct referrals (Level 1). Indirect referral earnings for Level 2 and Level 3 have been disabled.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "total_referrals": 2,
    "referrals": [
      {
        "id": 10,
        "username": "referraluser1",
        "package_name": "Premium Package",
        "registered_at": "2025-09-04T10:00:00.000000Z"
      },
      {
        "id": 11,
        "username": "referraluser2",
        "package_name": "Basic Package",
        "registered_at": "2025-09-05T10:00:00.000000Z"
      }
    ]
  },
  "message": "Referral statistics retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Withdrawal Requests
**GET** `/wallet/withdrawals`

Retrieve user's withdrawal requests.

**Headers:**
```
Authorization: Bearer <token>
```

**Query Parameters:**
- `status`: Filter by status (pending, approved, rejected, cancelled) (optional)
- `limit`: Number of items to retrieve (optional, default: 10)
- `page`: Page number (optional, default: 1)

**Success Response:**
```json
{
  "success": true,
  "data": {
    "withdrawals": [
      {
        "id": "1",
        "userId": "1",
        "amount": 50.0,
        "currency": "NGN",
        "paymentMethod": "bank_transfer",
        "paymentDetails": {
          "accountName": "John Doe",
          "accountNumber": "1234567890",
          "bankName": "Bank of America"
        },
        "status": "pending",
        "processedAt": null,
        "adminNotes": null,
        "createdAt": "2025-09-04T10:00:00.000000Z",
        "updatedAt": "2025-09-04T10:00:00.000000Z"
      }
    ],
    "meta": {
      "pagination": {
        "total": 1,
        "count": 1,
        "per_page": 10,
        "current_page": 1,
        "total_pages": 1
      }
    }
  },
  "message": "Withdrawals retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Cancel Withdrawal Request
**POST** `/wallet/withdrawals/{id}/cancel`

Cancel a pending withdrawal request.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "message": "Withdrawal request cancelled successfully",
  "data": {
    "withdrawal": {
      "id": "1",
      "userId": "1",
      "amount": 50.0,
      "currency": "NGN",
      "paymentMethod": "bank_transfer",
      "paymentDetails": {
        "accountName": "John Doe",
        "accountNumber": "1234567890",
        "bankName": "Bank of America"
      },
      "status": "cancelled",
      "processedAt": "2025-09-04T11:00:00.000000Z",
      "adminNotes": "Cancelled by user",
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "updatedAt": "2025-09-04T11:00:00.000000Z"
    }
  }
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Withdrawal request not found or cannot be cancelled

### Add Funds to Wallet
**POST** `/wallet/add-funds`

Add funds to wallet (for testing or admin purposes).

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "amount": "number (1-1000)",
  "description": "string (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Funds added successfully",
  "data": {
    "transaction": {
      "id": "2",
      "userId": "1",
      "type": "earning",
      "amount": 100.0,
      "currency": "NGN",
      "description": "Wallet top-up",
      "status": "completed",
      "referenceType": null,
      "referenceId": null,
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "updatedAt": "2025-09-04T10:00:00.000000Z"
    },
    "amountAdded": 100,
    "newBalance": 150.5
  }
}
```

**Error Responses:**
- 401: Unauthenticated
- 422: Validation errors

### Get Transaction Statistics
**GET** `/transactions/statistics`

Retrieve transaction statistics for charts/analytics.

**Headers:**
```
Authorization: Bearer <token>
```

**Query Parameters:**
- `period`: Number of days to retrieve data for (optional, default: 30)

**Success Response:**
```json
{
  "success": true,
  "data": {
    "periodDays": 30,
    "dailyStats": [
      {
        "date": "2025-09-04",
        "earnings": 50,
        "spending": 0,
        "withdrawals": 20,
        "net": 30
      }
    ],
    "typeDistribution": [
      {
        "type": "earning",
        "totalAmount": 150,
        "count": 5
      }
    ],
    "monthlyComparison": {
      "thisMonth": [
        {
          "type": "earning",
          "amount": 150
        }
      ],
      "lastMonth": [
        {
          "type": "earning",
          "amount": 100
        }
      ]
    }
  },
  "message": "Statistics retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Export Transactions
**POST** `/transactions/export`

Export transaction history.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "format": "string (csv|excel)",
  "date_from": "string (YYYY-MM-DD) (optional)",
  "date_to": "string (YYYY-MM-DD) (optional)",
  "type": "string (earning|purchase|withdrawal) (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Export data generated successfully",
  "data": {
    "format": "csv",
    "totalRecords": 5,
    "transactions": [
      {
        "id": "1",
        "type": "earning",
        "amount": 10.5,
        "currency": "NGN",
        "description": "Advertisement view reward",
        "status": "completed",
        "reference_type": "App\\Models\\Advertisement",
        "reference_id": "5",
        "created_at": "2025-09-04T10:00:00.000000Z"
      }
    ]
  }
}
```

**Error Responses:**
- 401: Unauthenticated
- 422: Validation errors

### Get User Profile
**GET** `/user/profile`

Retrieve the authenticated user's profile information including bank account status.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "country": "USA",
      "has_bound_bank_account": true,
      "bank_account_bound_at": "2025-09-04T10:00:00.000000Z",
      "bank_account_holder_name": "John Doe",
      "bank_name": "Bank of America",
      "bank_branch": "Main Branch",
      "withdrawals_enabled": true,
      "can_request_withdrawal": true
    }
  }
}
```

**Error Responses:**
- 401: Unauthenticated

### Bind Bank Account
**POST** `/user/bank-account/bind`

Bind bank account details to the user profile (can only be done once).

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "bank_account_holder_name": "string",
  "bank_account_number": "string",
  "bank_name": "string",
  "bank_branch": "string (optional)",
  "bank_routing_number": "string (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Bank account details bound successfully.",
  "data": {
    "user": {
      "id": 1,
      "has_bound_bank_account": true,
      "bank_account_bound_at": "2025-09-04T10:00:00.000000Z",
      "bank_account_holder_name": "John Doe",
      "bank_name": "Bank of America",
      "bank_branch": "Main Branch"
    }
  }
}
```

**Error Responses:**
- 401: Unauthenticated
- 422: Validation errors or bank account already bound

### Get Withdrawal Status
**GET** `/user/withdrawal-status`

Check if withdrawals are enabled for the user.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
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

**Error Responses:**
- 401: Unauthenticated

### Get Available Packages
**GET** `/packages/available`

Retrieve all available packages.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "packages": [
      {
        "id": 1,
        "name": "Basic Plan",
        "description": "Basic package with limited features",
        "price": 9.99,
        "duration": 30,
        "benefits": [
          "Up to 10 projects",
          "Basic analytics",
          "Email support"
        ],
        "features": {
          "project_limit": 10,
          "storage_limit": "100MB",
          "processing_limit": 100
        },
        "daily_earning_limit": 100.00,
        "ad_limits": 50
      }
    ]
  },
  "message": "Available packages retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Create Advertisement (Admin Only)
**POST** `/ads`

Create a new advertisement.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "title": "string",
  "description": "string",
  "target_url": "string (URL)",
  "category": "string",
  "budget": "number",
  "reward_amount": "number",
  "start_date": "string (YYYY-MM-DD)",
  "end_date": "string (YYYY-MM-DD)",
  "status": "string (pending|active|paused|completed|rejected)",
  "image": "file (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "ad": {
      "id": "1",
      "title": "Summer Sale",
      "description": "50% off on all products",
      "imageUrl": "https://example.com/storage/ad-images/image.jpg",
      "targetUrl": "https://example.com/sale",
      "category": "banner",
      "rewardAmount": 0.5,
      "startDate": "2025-09-01T00:00:00.000000Z",
      "endDate": "2025-09-30T00:00:00.000000Z",
      "status": "active",
      "createdAt": "2025-09-01T10:00:00.000000Z"
    }
  },
  "message": "Advertisement created successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 403: Unauthorized (admin access required)
- 422: Validation errors

### Update Advertisement (Admin Only)
**PUT** `/ads/{id}`

Update an existing advertisement.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "title": "string (optional)",
  "description": "string (optional)",
  "target_url": "string (URL) (optional)",
  "category": "string (optional)",
  "budget": "number (optional)",
  "reward_amount": "number (optional)",
  "start_date": "string (YYYY-MM-DD) (optional)",
  "end_date": "string (YYYY-MM-DD) (optional)",
  "status": "string (pending|active|paused|completed|rejected) (optional)",
  "image": "file (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "ad": {
      "id": "1",
      "title": "Updated Summer Sale",
      "description": "50% off on all products",
      "imageUrl": "https://example.com/storage/ad-images/image.jpg",
      "targetUrl": "https://example.com/sale",
      "category": "banner",
      "rewardAmount": 0.5,
      "startDate": "2025-09-01T00:00:00.000000Z",
      "endDate": "2025-09-30T00:00:00.000000Z",
      "status": "active",
      "createdAt": "2025-09-01T10:00:00.000000Z"
    }
  },
  "message": "Advertisement updated successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 403: Unauthorized (admin access required)
- 404: Advertisement not found
- 422: Validation errors

### Delete Advertisement (Admin Only)
**DELETE** `/ads/{id}`

Delete an advertisement.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "message": "Advertisement deleted successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 403: Unauthorized (admin access required)
- 404: Advertisement not found

### Get Support Options
**GET** `/support-options`

Retrieve available support options.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Technical Support",
      "description": "Get help with technical issues",
      "icon": "fa-headset",
      "whatsapp_link": "https://wa.me/1234567890?text=I%20need%20technical%20support"
    }
  ],
  "message": "Support options retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Product Categories
**GET** `/products/categories`

Retrieve available product categories.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "categories": [
      "Electronics",
      "Clothing",
      "Books",
      "Home & Garden"
    ]
  },
  "message": "Product categories retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Course Categories
**GET** `/courses/categories`

Retrieve available course categories.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "categories": [
      "Programming",
      "Design",
      "Business",
      "Marketing"
    ]
  },
  "message": "Course categories retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Brain Teaser Categories
**GET** `/brain-teasers/categories`

Retrieve available brain teaser categories.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "categories": [
      "Math",
      "Logic",
      "Riddle",
      "Trivia"
    ]
  },
  "message": "Brain teaser categories retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Daily Brain Teaser
**GET** `/brain-teasers/daily`

Retrieve the daily brain teaser.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "brainTeaser": {
      "id": "1",
      "title": "Daily Math Problem",
      "question": "What is 2+2?",
      "options": [
        "1",
        "2",
        "3",
        "4"
      ],
      "category": "Math",
      "difficulty": "easy",
      "rewardAmount": 10,
      "startDate": "2025-09-04T00:00:00.000000Z",
      "endDate": "2025-09-04T23:59:59.000000Z",
      "status": "active",
      "createdAt": "2025-09-04T10:00:00.000000Z",
      "userAttempt": {
        "attempted": false
      }
    }
  },
  "message": "Daily brain teaser retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Brain Teaser Leaderboard
**GET** `/brain-teasers/leaderboard`

Retrieve the brain teaser leaderboard.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "leaderboard": [
      {
        "rank": 1,
        "user": {
          "id": 1,
          "name": "John Doe"
        },
        "totalPoints": 100,
        "correctAnswers": 10
      }
    ]
  },
  "message": "Leaderboard retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get My Courses
**GET** `/courses/my-courses`

Retrieve courses created by the authenticated user.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": 1,
        "title": "Introduction to Programming",
        "description": "Learn the basics of programming",
        "category": "Programming",
        "price": 49.99,
        "status": "published",
        "enrollmentCount": 50,
        "rating": 4.5,
        "createdAt": "2025-09-01T10:00:00.000000Z"
      }
    ]
  },
  "message": "My courses retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get My Enrollments
**GET** `/courses/my-enrollments`

Retrieve courses the authenticated user is enrolled in.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "enrollments": [
      {
        "id": 1,
        "course": {
          "id": 5,
          "title": "Advanced Programming",
          "instructor": {
            "id": 2,
            "name": "Jane Smith"
          }
        },
        "progress": 75,
        "enrolledAt": "2025-09-01T10:00:00.000000Z",
        "completedAt": null
      }
    ]
  },
  "message": "My enrollments retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get My Products
**GET** `/products/my-products`

Retrieve products created by the authenticated user.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": 1,
        "name": "Smartphone",
        "description": "Latest model smartphone",
        "price": 599.99,
        "category": "Electronics",
        "status": "active",
        "soldCount": 10,
        "createdAt": "2025-09-01T10:00:00.000000Z"
      }
    ]
  },
  "message": "My products retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Create Product
**POST** `/products`

Create a new product.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "name": "string",
  "description": "string",
  "price": "number",
  "category": "string",
  "status": "string (active|inactive) (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "product": {
      "id": 1,
      "name": "Smartphone",
      "description": "Latest model smartphone",
      "price": 599.99,
      "category": "Electronics",
      "status": "active",
      "soldCount": 0,
      "createdAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Product created successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 422: Validation errors

### Update Product
**PUT** `/products/{id}`

Update an existing product.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "name": "string (optional)",
  "description": "string (optional)",
  "price": "number (optional)",
  "category": "string (optional)",
  "status": "string (active|inactive) (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "product": {
      "id": 1,
      "name": "Updated Smartphone",
      "description": "Latest model smartphone with new features",
      "price": 699.99,
      "category": "Electronics",
      "status": "active",
      "soldCount": 0,
      "createdAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Product updated successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Product not found
- 422: Validation errors

### Delete Product
**DELETE** `/products/{id}`

Delete a product.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Product not found

### Create Course
**POST** `/courses`

Create a new course.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "title": "string",
  "description": "string",
  "category": "string",
  "price": "number",
  "status": "string (draft|published) (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "course": {
      "id": 1,
      "title": "Introduction to Programming",
      "description": "Learn the basics of programming",
      "category": "Programming",
      "price": 49.99,
      "status": "draft",
      "enrollmentCount": 0,
      "rating": 0,
      "createdAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Course created successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 422: Validation errors

### Update Course
**PUT** `/courses/{id}`

Update an existing course.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "title": "string (optional)",
  "description": "string (optional)",
  "category": "string (optional)",
  "price": "number (optional)",
  "status": "string (draft|published) (optional)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "course": {
      "id": 1,
      "title": "Updated Introduction to Programming",
      "description": "Learn the basics of programming with new content",
      "category": "Programming",
      "price": 59.99,
      "status": "published",
      "enrollmentCount": 0,
      "rating": 0,
      "createdAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Course updated successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Course not found
- 422: Validation errors

### Delete Course
**DELETE** `/courses/{id}`

Delete a course.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "message": "Course deleted successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Course not found

### Enroll in Course
**POST** `/courses/{id}/enroll`

Enroll in a course.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "message": "Successfully enrolled in course",
  "data": {
    "enrollment": {
      "id": 1,
      "courseId": 5,
      "userId": 1,
      "progress": 0,
      "enrolledAt": "2025-09-04T10:00:00.000000Z"
    }
  }
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Course not found
- 400: Already enrolled

### Update Course Progress
**POST** `/courses/{id}/progress`

Update progress in a course.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "progress": "number (0-100)"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Course progress updated successfully",
  "data": {
    "progress": 75
  }
}
```

**Error Responses:**
- 401: Unauthenticated
- 404: Course not found
- 422: Validation errors

### Create Brain Teaser (Admin Only)
**POST** `/brain-teasers`

Create a new brain teaser.

**Headers:**
```
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "title": "string",
  "question": "string",
  "options": "array of strings",
  "correct_answer": "string",
  "category": "string",
  "difficulty": "string (easy|medium|hard)",
  "reward_amount": "number",
  "start_date": "string (YYYY-MM-DD)",
  "end_date": "string (YYYY-MM-DD)",
  "status": "string (draft|active|inactive)"
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "brainTeaser": {
      "id": "1",
      "title": "Math Problem",
      "question": "What is 2+2?",
      "options": [
        "1",
        "2",
        "3",
        "4"
      ],
      "correct_answer": "4",
      "category": "Math",
      "difficulty": "easy",
      "rewardAmount": 10,
      "startDate": "2025-09-04T00:00:00.000000Z",
      "endDate": "2025-09-10T00:00:00.000000Z",
      "status": "active",
      "createdAt": "2025-09-04T10:00:00.000000Z"
    }
  },
  "message": "Brain teaser created successfully"
}
```

**Error Responses:**
- 401: Unauthenticated
- 403: Unauthorized (admin access required)
- 422: Validation errors

### Get Dashboard Statistics
**GET** `/dashboard/stats`

Retrieve dashboard statistics.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "stats": {
      "totalProjects": 5,
      "totalEarnings": 150.5,
      "totalWithdrawals": 50.0,
      "pendingWithdrawals": 10.0,
      "activeCourses": 2,
      "enrolledCourses": 3
    }
  },
  "message": "Dashboard statistics retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Dashboard Earnings
**GET** `/dashboard/earnings`

Retrieve dashboard earnings data.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "earnings": {
      "thisMonth": 100.5,
      "lastMonth": 75.0,
      "total": 150.5
    }
  },
  "message": "Dashboard earnings retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Dashboard Notifications
**GET** `/dashboard/notifications`

Retrieve dashboard notifications.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": 1,
        "title": "New Course Enrollment",
        "message": "You have a new enrollment in your course",
        "type": "info",
        "read": false,
        "createdAt": "2025-09-04T10:00:00.000000Z"
      }
    ]
  },
  "message": "Dashboard notifications retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Dashboard System Stats
**GET** `/dashboard/system-stats`

Retrieve dashboard system statistics.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "systemStats": {
      "totalUsers": 100,
      "totalProjects": 500,
      "totalCourses": 50,
      "totalProducts": 200,
      "activeAdvertisements": 10
    }
  },
  "message": "System statistics retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

### Get Dashboard Available Ads
**GET** `/dashboard/available-ads`

Retrieve available advertisements for the dashboard.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "ads": [
      {
        "id": "1",
        "title": "Summer Sale",
        "description": "50% off on all products",
        "imageUrl": "https://example.com/storage/ad-images/image.jpg",
        "targetUrl": "https://example.com/sale",
        "category": "banner",
        "rewardAmount": 0.5,
        "startDate": "2025-09-01T00:00:00.000000Z",
        "endDate": "2025-09-30T00:00:00.000000Z",
        "status": "active",
        "createdAt": "2025-09-01T10:00:00.000000Z"
      }
    ]
  },
  "message": "Available advertisements retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

## Error Codes and Meanings

### HTTP Status Codes
- **200**: Success - Request was successful
- **201**: Created - Resource was successfully created
- **400**: Bad Request - Request could not be understood or was missing required parameters
- **401**: Unauthorized - Authentication failed or user doesn't have permissions for requested operation
- **403**: Forbidden - Access denied, user doesn't have permission to access this resource
- **404**: Not Found - Resource not found
- **422**: Unprocessable Entity - Request was well-formed but was unable to be followed due to semantic errors
- **500**: Internal Server Error - Something went wrong on the server

### Common Error Messages
- `"message": "Unauthenticated"` - Token is missing or invalid
- `"message": "Unauthorized. Admin access required."` - User doesn't have admin privileges
- `"message": "Validation failed"` - Request data doesn't meet validation requirements
- `"message": "Resource not found"` - Requested resource doesn't exist
- `"message": "Daily ad interaction limit reached"` - User has reached their daily ad interaction limit
- `"message": "Insufficient balance"` - User doesn't have enough funds for the requested operation

## Rate Limiting
API endpoints are rate-limited to prevent abuse:
- Authentication endpoints: 10 requests per minute
- All other endpoints: 60 requests per minute

When rate limit is exceeded, the API will return a 429 status code with a message indicating the limit has been exceeded.

## Data Types
All timestamps are returned in ISO 8601 format: `YYYY-MM-DDTHH:MM:SS.ssssssZ`

Currency amounts are returned as integers representing the smallest currency unit (cents for USD, etc.).

## Versioning
The API version is included in the URL path as `/api/v1/`. Future versions will be available as `/api/v2/`, etc.