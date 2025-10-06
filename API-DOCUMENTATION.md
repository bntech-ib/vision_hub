# VisionHub API Documentation

## ⚠️ DEPRECATED DOCUMENTATION

**This documentation is deprecated. Please refer to the [VisionHub API V2 Documentation](VISIONHUB_API_V2_DOCUMENTATION.md) for the complete and up-to-date API documentation.**

## Overview
This document provides comprehensive documentation for the VisionHub API endpoints, including request/response formats and error handling.

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
``json
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
  "action": "string (view|click)"
}
```

**Success Response:**
``json
{
  "success": true,
  "data": {
    "interaction": {
      "id": "1",
      "adId": "1",
      "userId": "1",
      "action": "view",
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

### Get Transactions
**GET** `/transactions`

Retrieve the user's transactions.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response:**
``json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": "1",
        "transactionId": "TXN_1234567890",
        "userId": "1",
        "type": "earning",
        "amount": 10.5,
        "description": "Advertisement view reward",
        "status": "completed",
        "createdAt": "2025-09-04T10:00:00.000000Z"
      }
    ]
  },
  "message": "Transactions retrieved successfully"
}
```

**Error Responses:**
- 401: Unauthenticated

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
    "wallet": {
      "balance": 100.5,
      "totalEarnings": 150.0,
      "totalWithdrawals": 49.5,
      "pendingWithdrawals": 10.0
    }
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
- 422: Validation errors
- 400: Insufficient balance