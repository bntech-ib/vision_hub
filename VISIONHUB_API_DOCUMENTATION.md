# VisionHub API Documentation

## 📋 Project Overview

VisionHub is a comprehensive online platform that combines multiple monetization and learning features in a single ecosystem. The platform allows users to earn money through various activities such as viewing advertisements, participating in brain teasers, and referring other users. Additionally, it provides educational content through online courses and a marketplace for buying and selling products.

### Key Features

1. **User Authentication & Management**
   - User registration with referral system
   - Secure login/logout functionality
   - Profile management
   - Package/subscription system

2. **Advertisement System**
   - Ad viewing and interaction tracking
   - Earning system based on ad engagement
   - Advertiser management interface

3. **Marketplace**
   - Product listing and management
   - Purchase and sales functionality
   - Category-based browsing

4. **Online Learning Platform**
   - Course creation and enrollment
   - Progress tracking
   - Course categories and levels

5. **Brain Teasers & Gamification**
   - Interactive brain teasers with different categories
   - Points-based reward system
   - Leaderboards and statistics

6. **Financial System**
   - Wallet management
   - Transaction history
   - Withdrawal requests
   - Earning tracking

7. **Dashboard & Analytics**
   - Personal dashboard with statistics
   - Earning history
   - Notification system

## 🏗️ Technical Architecture

- **Backend Framework**: Laravel 10.x
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum
- **API Standards**: RESTful JSON API
- **Rate Limiting**: Built-in API throttling
- **Caching**: Redis (optional)

## 🔐 Authentication

All protected endpoints require authentication via Laravel Sanctum tokens. To authenticate:

1. Register or login to obtain a token
2. Include the token in the Authorization header for subsequent requests:

```
Authorization: Bearer YOUR_API_TOKEN
Content-Type: application/json
Accept: application/json
```

## 🌐 Base URLs

- **Development**: `http://localhost:8000/api/v1`
- **Production**: `https://api.visionhub.com/api/v1`

## 📡 API Response Format

### Success Response

```json
{
  "success": true,
  "data": {},
  "message": "Operation successful",
  "meta": {
    "pagination": {},
    "timestamp": "2024-01-20T10:00:00Z"
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  },
  "meta": {
    "timestamp": "2024-01-20T10:00:00Z"
  }
}
```

## 🚦 Rate Limiting

To prevent abuse, the API implements rate limiting:
- 60 requests per minute per IP address
- 1000 requests per hour per authenticated user

When rate limits are exceeded, the API returns a 429 (Too Many Requests) status code.

## 🔐 HTTP Status Codes

- **200**: OK - Request successful
- **201**: Created - Resource created successfully
- **400**: Bad Request - Invalid request parameters
- **401**: Unauthorized - Authentication required
- **403**: Forbidden - Insufficient permissions
- **404**: Not Found - Resource not found
- **422**: Unprocessable Entity - Validation errors
- **500**: Internal Server Error - Server error

---

# 📚 API Endpoints

## 🔐 Authentication Endpoints

### POST /api/v1/auth/register

Registers a new user in the system.

**Request Body:**
```json
{
  "fullName": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "password": "password123",
  "confirmPassword": "password123",
  "packageId": "1",
  "referrerCode": "VH123456",
  "country": "Nigeria",
  "phone": "+2341234567890"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "username": "johndoe",
      "email": "john@example.com",
      "fullName": "John Doe",
      "phone": "+2341234567890",
      "country": "Nigeria",
      "package": {
        "id": "1",
        "name": "Silver Package",
        "price": 5000,
        "benefits": ["Daily earnings up to ₦500", "Referral bonuses", "Access to marketplace"],
        "duration": 30
      },
      "referralCode": "VHABCDEF",
      "referralStats": {
        "level1Count": 5,
        "level2Count": 3,
        "level3Count": 2,
        "totalCount": 10
      },
      "referralEarnings": {
        "level1Earnings": 500.00,
        "level2Earnings": 150.00,
        "level3Earnings": 50.00,
        "totalEarnings": 700.00
      },
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Registration successful! Welcome to VisionHub!"
}
```

### POST /api/v1/auth/login

Authenticates a user and returns an access token.

**Request Body:**
```json
{
  "username": "johndoe",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "username": "johndoe",
      "email": "john@example.com",
      "fullName": "John Doe",
      "phone": "+2341234567890",
      "country": "Nigeria",
      "package": {
        "id": "1",
        "name": "Silver Package",
        "price": 5000,
        "benefits": ["Daily earnings up to ₦500", "Referral bonuses", "Access to marketplace"],
        "duration": 30
      },
      "referralCode": "VHABCDEF",
      "referralStats": {
        "level1Count": 5,
        "level2Count": 3,
        "level3Count": 2,
        "totalCount": 10
      },
      "referralEarnings": {
        "level1Earnings": 500.00,
        "level2Earnings": 150.00,
        "level3Earnings": 50.00,
        "totalEarnings": 700.00
      },
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456"
  },
  "message": "Login successful"
}
```

### POST /api/v1/auth/login/email

Alternative login method using email instead of username.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "username": "johndoe",
      "email": "john@example.com",
      "fullName": "John Doe",
      "phone": "+2341234567890",
      "country": "Nigeria",
      "package": {
        "id": "1",
        "name": "Silver Package",
        "price": 5000,
        "benefits": ["Daily earnings up to ₦500", "Referral bonuses", "Access to marketplace"],
        "duration": 30
      },
      "referralCode": "VHABCDEF",
      "referralStats": {
        "level1Count": 5,
        "level2Count": 3,
        "level3Count": 2,
        "totalCount": 10
      },
      "referralEarnings": {
        "level1Earnings": 500.00,
        "level2Earnings": 150.00,
        "level3Earnings": 50.00,
        "totalEarnings": 700.00
      },
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456"
  },
  "message": "Login successful"
}
```

### POST /api/v1/auth/logout

Logs out the authenticated user (revokes current token).

**Success Response (200):**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

### POST /api/v1/auth/logout-all

Logs out the user from all devices (revokes all tokens).

**Success Response (200):**
```json
{
  "success": true,
  "message": "Successfully logged out from all devices"
}
```

### GET /api/v1/auth/user

Retrieves the authenticated user's information.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "username": "johndoe",
      "email": "john@example.com",
      "fullName": "John Doe",
      "phone": "+2341234567890",
      "country": "Nigeria",
      "package": {
        "id": "1",
        "name": "Silver Package",
        "price": 5000,
        "benefits": ["Daily earnings up to ₦500", "Referral bonuses", "Access to marketplace"],
        "duration": 30
      },
      "referralCode": "VHABCDEF",
      "referralStats": {
        "level1Count": 5,
        "level2Count": 3,
        "level3Count": 2,
        "totalCount": 10
      },
      "referralEarnings": {
        "level1Earnings": 500.00,
        "level2Earnings": 150.00,
        "level3Earnings": 50.00,
        "totalEarnings": 700.00
      },
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "User data retrieved successfully"
}
```

### PUT /api/v1/auth/profile

Updates the authenticated user's profile information.

**Request Body:**
```json
{
  "fullName": "John Smith",
  "phone": "+2341234567891",
  "country": "Ghana"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "username": "johndoe",
      "email": "john@example.com",
      "fullName": "John Smith",
      "phone": "+2341234567891",
      "country": "Ghana",
      "package": {
        "id": "1",
        "name": "Silver Package",
        "price": 5000,
        "benefits": ["Daily earnings up to ₦500", "Referral bonuses", "Access to marketplace"],
        "duration": 30
      },
      "referralCode": "VHABCDEF",
      "referralStats": {
        "level1Count": 5,
        "level2Count": 3,
        "level3Count": 2,
        "totalCount": 10
      },
      "referralEarnings": {
        "level1Earnings": 500.00,
        "level2Earnings": 150.00,
        "level3Earnings": 50.00,
        "totalEarnings": 700.00
      },
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T11:00:00Z"
    }
  },
  "message": "Profile updated successfully"
}
```

### PUT /api/v1/auth/change-password

Changes the authenticated user's password.

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "password": "newpassword456",
  "password_confirmation": "newpassword456"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Password changed successfully."
}
```

### GET /api/v1/auth/tokens

Retrieves all active API tokens for the authenticated user.

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "auth-token",
      "created_at": "2024-01-20T10:00:00Z",
      "last_used_at": "2024-01-20T10:30:00Z"
    }
  ]
}
```

### POST /api/v1/auth/tokens

Creates a new API token for the authenticated user.

**Request Body:**
```json
{
  "name": "mobile-app"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "1|abcdefghijklmnopqrstuvwxyz123456"
  },
  "message": "Token created successfully"
}
```

### DELETE /api/v1/auth/tokens

Revokes/delete an API token.

**Request Body:**
```json
{
  "id": 1
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Token revoked successfully"
}
```

## 📊 Dashboard Endpoints

### GET /api/v1/dashboard/stats

Retrieves dashboard statistics for the authenticated user.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "stats": {
      "totalEarnings": 74349.00,
      "availableBalance": 12500.50,
      "pendingEarnings": 3200.75,
      "totalWithdrawals": 45600.25,
      "referralCount": 12,
      "activeAds": 5,
      "currency": "NGN"
    }
  },
  "message": "Dashboard stats retrieved successfully"
}
```

### GET /api/v1/dashboard/earnings

Retrieves earnings history for the authenticated user.

**Query Parameters:**
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "earnings": [
      {
        "id": "1",
        "amount": 250.00,
        "source": "ad_view",
        "description": "Earning from Ad View",
        "date": "2024-01-20T08:00:00Z"
      },
      {
        "id": "2",
        "amount": 500.00,
        "source": "referral",
        "description": "New Referral Bonus",
        "date": "2024-01-20T05:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 2,
      "count": 2,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Earnings history retrieved successfully"
}
```

### GET /api/v1/dashboard/notifications

Retrieves notifications for the authenticated user.

**Query Parameters:**
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": "1",
        "userId": "1",
        "title": "Payment Received",
        "message": "You've received ₦250.00 from ad view",
        "type": "earning",
        "read": false,
        "createdAt": "2024-01-20T08:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Notifications retrieved successfully"
}
```

### GET /api/v1/dashboard/system-stats

Retrieves system statistics for the authenticated user.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "stats": {
      "totalUsers": 1250,
      "totalAds": 45,
      "totalCourses": 18,
      "totalProducts": 230,
      "activeUsers": 890
    }
  },
  "message": "System stats retrieved successfully"
}
```

### GET /api/v1/dashboard/available-ads

Retrieves available advertisements for the authenticated user.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "ads": [
      {
        "id": "1",
        "title": "Invest in Cryptocurrency",
        "description": "Start your crypto journey with our secure platform. Get ₦1000 bonus on signup!",
        "imageUrl": "https://api.visionhub.com/storage/ads/1/image.jpg",
        "targetUrl": "https://crypto-platform.com",
        "category": "finance",
        "reward": 25,
        "timeLimit": 30
      }
    ]
  },
  "message": "Available ads retrieved successfully"
}
```

## 📰 Advertisement Endpoints

### GET /api/v1/ads

Retrieves all available advertisements.

**Query Parameters:**
- category (optional): Filter by category
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "ads": [
      {
        "id": "1",
        "advertiserId": "advertiser1",
        "title": "Invest in Cryptocurrency",
        "description": "Start your crypto journey with our secure platform. Get ₦1000 bonus on signup!",
        "imageUrl": "https://api.visionhub.com/storage/ads/1/image.jpg",
        "targetUrl": "https://crypto-platform.com",
        "category": "finance",
        "budget": 100000,
        "spent": 25000,
        "impressions": 5000,
        "clicks": 250,
        "status": "active",
        "startDate": "2024-01-01T00:00:00Z",
        "endDate": "2024-02-01T00:00:00Z",
        "createdAt": "2024-01-01T00:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Advertisements retrieved successfully"
}
```

### POST /api/v1/ads

Creates a new advertisement (requires admin privileges).

**Request Body:**
```json
{
  "title": "New Advertisement",
  "description": "Description of the advertisement",
  "imageUrl": "https://example.com/image.jpg",
  "targetUrl": "https://target-website.com",
  "category": "technology",
  "budget": 50000,
  "startDate": "2024-01-20T00:00:00Z",
  "endDate": "2024-02-20T00:00:00Z"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "ad": {
      "id": "2",
      "advertiserId": "1",
      "title": "New Advertisement",
      "description": "Description of the advertisement",
      "imageUrl": "https://api.visionhub.com/storage/ads/2/image.jpg",
      "targetUrl": "https://target-website.com",
      "category": "technology",
      "budget": 50000,
      "spent": 0,
      "impressions": 0,
      "clicks": 0,
      "status": "active",
      "startDate": "2024-01-20T00:00:00Z",
      "endDate": "2024-02-20T00:00:00Z",
      "createdAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Advertisement created successfully"
}
```

### GET /api/v1/ads/{id}

Retrieves a specific advertisement by ID.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "ad": {
      "id": "1",
      "advertiserId": "advertiser1",
      "title": "Invest in Cryptocurrency",
      "description": "Start your crypto journey with our secure platform. Get ₦1000 bonus on signup!",
      "imageUrl": "https://api.visionhub.com/storage/ads/1/image.jpg",
      "targetUrl": "https://crypto-platform.com",
      "category": "finance",
      "budget": 100000,
      "spent": 25000,
      "impressions": 5000,
      "clicks": 250,
      "status": "active",
      "startDate": "2024-01-01T00:00:00Z",
      "endDate": "2024-02-01T00:00:00Z",
      "createdAt": "2024-01-01T00:00:00Z"
    }
  },
  "message": "Advertisement retrieved successfully"
}
```

### PUT /api/v1/ads/{id}

Updates an existing advertisement (requires admin privileges).

**Request Body:**
```json
{
  "title": "Updated Advertisement",
  "description": "Updated description of the advertisement",
  "budget": 75000
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "ad": {
      "id": "1",
      "advertiserId": "advertiser1",
      "title": "Updated Advertisement",
      "description": "Updated description of the advertisement",
      "imageUrl": "https://api.visionhub.com/storage/ads/1/image.jpg",
      "targetUrl": "https://crypto-platform.com",
      "category": "finance",
      "budget": 75000,
      "spent": 25000,
      "impressions": 5000,
      "clicks": 250,
      "status": "active",
      "startDate": "2024-01-01T00:00:00Z",
      "endDate": "2024-02-01T00:00:00Z",
      "createdAt": "2024-01-01T00:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Advertisement updated successfully"
}
```

### DELETE /api/v1/ads/{id}

Deletes an advertisement (requires admin privileges).

**Success Response (200):**
```json
{
  "success": true,
  "message": "Advertisement deleted successfully"
}
```

### POST /api/v1/ads/{id}/interact

Records an interaction (view or click) with an advertisement.

**Request Body:**
```json
{
  "type": "view" // or "click"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "interaction": {
      "id": "100",
      "userId": "1",
      "adId": "1",
      "type": "view",
      "earnings": 25,
      "timestamp": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Ad interaction recorded successfully"
}
```

### GET /api/v1/ads/history/my-interactions

Retrieves the authenticated user's ad interaction history.

**Query Parameters:**
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "interactions": [
      {
        "id": "100",
        "userId": "1",
        "adId": "1",
        "ad": {
          "id": "1",
          "title": "Invest in Cryptocurrency"
        },
        "type": "view",
        "earnings": 25,
        "timestamp": "2024-01-20T10:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Ad interactions retrieved successfully"
}
```

## 🛒 Marketplace Endpoints

### GET /api/v1/products

Retrieves all available products in the marketplace.

**Query Parameters:**
- category (optional): Filter by category
- search (optional): Search term for product title/description
- minPrice (optional): Minimum price filter
- maxPrice (optional): Maximum price filter
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": "1",
        "sellerId": "seller1",
        "title": "iPhone 14 Pro Max",
        "description": "Brand new iPhone 14 Pro Max 256GB in Deep Purple. Includes original accessories and warranty.",
        "price": 650000,
        "currency": "NGN",
        "category": "electronics",
        "images": [
          "https://api.visionhub.com/storage/products/1/image1.jpg",
          "https://api.visionhub.com/storage/products/1/image2.jpg"
        ],
        "status": "active",
        "stock": 5,
        "location": "Lagos, Nigeria",
        "createdAt": "2024-01-20T00:00:00Z",
        "updatedAt": "2024-01-20T00:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Products retrieved successfully"
}
```

### POST /api/v1/products

Creates a new product listing.

**Request Body:**
```json
{
  "title": "New Product",
  "description": "Description of the new product",
  "price": 25000,
  "category": "electronics",
  "images": [
    "https://example.com/image1.jpg",
    "https://example.com/image2.jpg"
  ],
  "stock": 10,
  "location": "Abuja, Nigeria"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "product": {
      "id": "7",
      "sellerId": "1",
      "title": "New Product",
      "description": "Description of the new product",
      "price": 25000,
      "currency": "NGN",
      "category": "electronics",
      "images": [
        "https://api.visionhub.com/storage/products/7/image1.jpg",
        "https://api.visionhub.com/storage/products/7/image2.jpg"
      ],
      "status": "pending_review",
      "stock": 10,
      "location": "Abuja, Nigeria",
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Product created successfully"
}
```

### GET /api/v1/products/{id}

Retrieves a specific product by ID.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "product": {
      "id": "1",
      "sellerId": "seller1",
      "title": "iPhone 14 Pro Max",
      "description": "Brand new iPhone 14 Pro Max 256GB in Deep Purple. Includes original accessories and warranty.",
      "price": 650000,
      "currency": "NGN",
      "category": "electronics",
      "images": [
        "https://api.visionhub.com/storage/products/1/image1.jpg",
        "https://api.visionhub.com/storage/products/1/image2.jpg"
      ],
      "status": "active",
      "stock": 5,
      "location": "Lagos, Nigeria",
      "createdAt": "2024-01-20T00:00:00Z",
      "updatedAt": "2024-01-20T00:00:00Z"
    }
  },
  "message": "Product retrieved successfully"
}
```

### PUT /api/v1/products/{id}

Updates an existing product listing.

**Request Body:**
```json
{
  "title": "Updated Product Title",
  "price": 30000,
  "stock": 8
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "product": {
      "id": "1",
      "sellerId": "seller1",
      "title": "Updated Product Title",
      "description": "Brand new iPhone 14 Pro Max 256GB in Deep Purple. Includes original accessories and warranty.",
      "price": 30000,
      "currency": "NGN",
      "category": "electronics",
      "images": [
        "https://api.visionhub.com/storage/products/1/image1.jpg",
        "https://api.visionhub.com/storage/products/1/image2.jpg"
      ],
      "status": "active",
      "stock": 8,
      "location": "Lagos, Nigeria",
      "createdAt": "2024-01-20T00:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Product updated successfully"
}
```

### DELETE /api/v1/products/{id}

Deletes a product listing.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

### GET /api/v1/products/categories

Retrieves all available product categories.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "value": "electronics",
        "label": "Electronics"
      },
      {
        "value": "fashion",
        "label": "Fashion"
      },
      {
        "value": "home",
        "label": "Home & Living"
      },
      {
        "value": "books",
        "label": "Books"
      },
      {
        "value": "services",
        "label": "Services"
      },
      {
        "value": "digital",
        "label": "Digital Products"
      },
      {
        "value": "other",
        "label": "Other"
      }
    ]
  },
  "message": "Categories retrieved successfully"
}
```

### POST /api/v1/products/{id}/purchase

Purchases a product.

**Request Body:**
```json
{
  "quantity": 1
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "transaction": {
      "id": "500",
      "userId": "1",
      "productId": "1",
      "amount": 650000,
      "currency": "NGN",
      "status": "completed",
      "quantity": 1,
      "timestamp": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Product purchased successfully"
}
```

### GET /api/v1/products/my-products

Retrieves all products listed by the authenticated user.

**Query Parameters:**
- status (optional): Filter by product status
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": "7",
        "sellerId": "1",
        "title": "New Product",
        "price": 25000,
        "currency": "NGN",
        "category": "electronics",
        "status": "pending_review",
        "stock": 10,
        "location": "Abuja, Nigeria",
        "createdAt": "2024-01-20T10:00:00Z",
        "updatedAt": "2024-01-20T10:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Your products retrieved successfully"
}
```

### GET /api/v1/products/purchase-history

Retrieves the authenticated user's product purchase history.

**Query Parameters:**
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "purchases": [
      {
        "id": "500",
        "userId": "1",
        "productId": "1",
        "product": {
          "id": "1",
          "title": "iPhone 14 Pro Max"
        },
        "amount": 650000,
        "currency": "NGN",
        "status": "completed",
        "quantity": 1,
        "timestamp": "2024-01-20T10:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Purchase history retrieved successfully"
}
```

### GET /api/v1/products/sales-history

Retrieves the authenticated user's product sales history.

**Query Parameters:**
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "sales": [
      {
        "id": "500",
        "userId": "2",
        "productId": "7",
        "product": {
          "id": "7",
          "title": "New Product"
        },
        "amount": 25000,
        "currency": "NGN",
        "status": "completed",
        "quantity": 1,
        "timestamp": "2024-01-20T10:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Sales history retrieved successfully"
}
```

## 🎓 Course Endpoints

### GET /api/v1/courses

Retrieves all available courses.

**Query Parameters:**
- category (optional): Filter by category
- level (optional): Filter by difficulty level
- search (optional): Search term for course title/description
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": "1",
        "title": "Complete Web Development Course",
        "description": "Learn to build modern web applications with HTML, CSS, JavaScript, and React.",
        "instructor": "John Smith",
        "instructorImage": "https://api.visionhub.com/storage/instructors/1/image.jpg",
        "thumbnailUrl": "https://api.visionhub.com/storage/courses/1/thumbnail.jpg",
        "category": "programming",
        "level": "beginner",
        "duration": 1200,
        "lessonsCount": 50,
        "price": 15000,
        "originalPrice": 25000,
        "rating": 4.8,
        "studentsCount": 1250,
        "isEnrolled": false,
        "isPremium": false,
        "tags": ["web", "javascript", "react"],
        "createdAt": "2024-01-01T00:00:00Z",
        "updatedAt": "2024-01-01T00:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Courses retrieved successfully"
}
```

### GET /api/v1/courses/categories

Retrieves all available course categories.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "value": "programming",
        "label": "Programming"
      },
      {
        "value": "design",
        "label": "Design"
      },
      {
        "value": "business",
        "label": "Business"
      },
      {
        "value": "marketing",
        "label": "Marketing"
      }
    ]
  },
  "message": "Categories retrieved successfully"
}
```

### GET /api/v1/courses/{id}

Retrieves a specific course by ID.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "course": {
      "id": "1",
      "title": "Complete Web Development Course",
      "description": "Learn to build modern web applications with HTML, CSS, JavaScript, and React.",
      "instructor": "John Smith",
      "instructorImage": "https://api.visionhub.com/storage/instructors/1/image.jpg",
      "thumbnailUrl": "https://api.visionhub.com/storage/courses/1/thumbnail.jpg",
      "category": "programming",
      "level": "beginner",
      "duration": 1200,
      "lessonsCount": 50,
      "price": 15000,
      "originalPrice": 25000,
      "rating": 4.8,
      "studentsCount": 1250,
      "isEnrolled": false,
      "isPremium": false,
      "tags": ["web", "javascript", "react"],
      "createdAt": "2024-01-01T00:00:00Z",
      "updatedAt": "2024-01-01T00:00:00Z"
    }
  },
  "message": "Course retrieved successfully"
}
```

### POST /api/v1/courses/{id}/enroll

Enrolls the authenticated user in a course.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "enrollment": {
      "id": "100",
      "userId": "1",
      "courseId": "1",
      "progress": 0,
      "completedLessons": [],
      "enrolledAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Successfully enrolled in course"
}
```

### GET /api/v1/courses/my-enrollments

Retrieves all courses the authenticated user is enrolled in.

**Query Parameters:**
- status (optional): Filter by enrollment status
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "enrollments": [
      {
        "id": "100",
        "userId": "1",
        "courseId": "1",
        "progress": 25,
        "completedLessons": ["1", "2", "3"],
        "enrolledAt": "2024-01-20T10:00:00Z",
        "course": {
          "id": "1",
          "title": "Complete Web Development Course",
          "thumbnailUrl": "https://api.visionhub.com/storage/courses/1/thumbnail.jpg",
          "instructor": "John Smith",
          "duration": 1200,
          "lessonsCount": 50
        }
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Enrollments retrieved successfully"
}
```

### POST /api/v1/courses/{id}/progress

Updates the user's progress in a course.

**Request Body:**
```json
{
  "lessonId": "4",
  "completed": true
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "enrollment": {
      "id": "100",
      "userId": "1",
      "courseId": "1",
      "progress": 30,
      "completedLessons": ["1", "2", "3", "4"],
      "enrolledAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Progress updated successfully"
}
```

### POST /api/v1/courses

Creates a new course (requires instructor privileges).

**Request Body:**
```json
{
  "title": "New Course",
  "description": "Description of the new course",
  "category": "programming",
  "level": "beginner",
  "price": 10000,
  "duration": 600,
  "thumbnail": "image_file",
  "curriculum": [
    {
      "title": "Introduction",
      "duration": 30
    }
  ],
  "tags": "web,html,css"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "course": {
      "id": "2",
      "instructorId": "1",
      "title": "New Course",
      "description": "Description of the new course",
      "category": "programming",
      "level": "beginner",
      "price": 10000,
      "durationHours": 600,
      "thumbnail": "https://api.visionhub.com/storage/courses/2/thumbnail.jpg",
      "curriculum": [
        {
          "title": "Introduction",
          "duration": 30
        }
      ],
      "tags": "web,html,css",
      "status": "pending_review",
      "enrollmentCount": 0,
      "rating": null,
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Course created successfully"
}
```

### GET /api/v1/courses/my-courses

Retrieves all courses created by the authenticated user (instructor).

**Query Parameters:**
- status (optional): Filter by course status
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": "2",
        "instructorId": "1",
        "title": "New Course",
        "description": "Description of the new course",
        "category": "programming",
        "level": "beginner",
        "price": 10000,
        "durationHours": 600,
        "thumbnail": "https://api.visionhub.com/storage/courses/2/thumbnail.jpg",
        "status": "pending_review",
        "enrollmentCount": 0,
        "rating": null,
        "createdAt": "2024-01-20T10:00:00Z",
        "updatedAt": "2024-01-20T10:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Your courses retrieved successfully"
}
```

### PUT /api/v1/courses/{id}

Updates an existing course.

**Request Body:**
```json
{
  "title": "Updated Course Title",
  "price": 12000
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "course": {
      "id": "2",
      "instructorId": "1",
      "title": "Updated Course Title",
      "description": "Description of the new course",
      "category": "programming",
      "level": "beginner",
      "price": 12000,
      "durationHours": 600,
      "thumbnail": "https://api.visionhub.com/storage/courses/2/thumbnail.jpg",
      "status": "pending_review",
      "enrollmentCount": 0,
      "rating": null,
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T11:00:00Z"
    }
  },
  "message": "Course updated successfully"
}
```

### DELETE /api/v1/courses/{id}

Deletes a course.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Course deleted successfully"
}
```

## 🧠 Brain Teaser Endpoints

### GET /api/v1/brain-teasers

Retrieves all available brain teasers.

**Query Parameters:**
- category (optional): Filter by category
- difficulty (optional): Filter by difficulty level
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "brainTeasers": [
      {
        "id": "1",
        "title": "Logic Puzzle",
        "question": "If all Bloops are Razzies and some Razzies are Loppies, then are all Bloops definitely Loppies?",
        "options": [
          "Yes",
          "No",
          "Maybe",
          "Cannot be determined"
        ],
        "correctAnswer": 1,
        "explanation": "Since only some Razzies are Loppies, we cannot conclude that all Bloops (which are all Razzies) are Loppies.",
        "difficulty": "medium",
        "category": "logic",
        "points": 20,
        "timeLimit": 60,
        "imageUrl": "https://api.visionhub.com/storage/brainteasers/1/image.jpg",
        "createdAt": "2024-01-01T00:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Brain teasers retrieved successfully"
}
```

### GET /api/v1/brain-teasers/categories

Retrieves all available brain teaser categories.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "value": "logic",
        "label": "Logic"
      },
      {
        "value": "math",
        "label": "Mathematics"
      },
      {
        "value": "riddle",
        "label": "Riddles"
      }
    ]
  },
  "message": "Categories retrieved successfully"
}
```

### GET /api/v1/brain-teasers/daily

Retrieves the daily brain teaser.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "brainTeaser": {
      "id": "1",
      "title": "Logic Puzzle",
      "question": "If all Bloops are Razzies and some Razzies are Loppies, then are all Bloops definitely Loppies?",
      "options": [
        "Yes",
        "No",
        "Maybe",
        "Cannot be determined"
      ],
      "difficulty": "medium",
      "category": "logic",
      "points": 20,
      "timeLimit": 60,
      "imageUrl": "https://api.visionhub.com/storage/brainteasers/1/image.jpg",
      "createdAt": "2024-01-01T00:00:00Z"
    }
  },
  "message": "Daily brain teaser retrieved successfully"
}
```

### GET /api/v1/brain-teasers/leaderboard

Retrieves the brain teaser leaderboard.

**Query Parameters:**
- limit (optional): Number of records to return (default: 10)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "leaderboard": [
      {
        "userId": "1",
        "username": "johndoe",
        "totalPoints": 450,
        "correctAnswers": 18,
        "totalAttempts": 25
      }
    ]
  },
  "message": "Leaderboard retrieved successfully"
}
```

### GET /api/v1/brain-teasers/{id}

Retrieves a specific brain teaser by ID.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "brainTeaser": {
      "id": "1",
      "title": "Logic Puzzle",
      "question": "If all Bloops are Razzies and some Razzies are Loppies, then are all Bloops definitely Loppies?",
      "options": [
        "Yes",
        "No",
        "Maybe",
        "Cannot be determined"
      ],
      "difficulty": "medium",
      "category": "logic",
      "points": 20,
      "timeLimit": 60,
      "imageUrl": "https://api.visionhub.com/storage/brainteasers/1/image.jpg",
      "createdAt": "2024-01-01T00:00:00Z"
    }
  },
  "message": "Brain teaser retrieved successfully"
}
```

### POST /api/v1/brain-teasers/{id}/submit

Submits an answer to a brain teaser.

**Request Body:**
```json
{
  "selectedAnswer": 1
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "attempt": {
      "id": "500",
      "userId": "1",
      "brainTeaserId": "1",
      "selectedAnswer": 1,
      "isCorrect": true,
      "pointsEarned": 20,
      "timeSpent": 25,
      "attemptedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Attempt recorded successfully"
}
```

### GET /api/v1/brain-teasers/my-attempts

Retrieves the authenticated user's brain teaser attempts.

**Query Parameters:**
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "attempts": [
      {
        "id": "500",
        "userId": "1",
        "brainTeaserId": "1",
        "brainTeaser": {
          "id": "1",
          "title": "Logic Puzzle"
        },
        "selectedAnswer": 1,
        "isCorrect": true,
        "pointsEarned": 20,
        "timeSpent": 25,
        "attemptedAt": "2024-01-20T10:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Your attempts retrieved successfully"
}
```

### GET /api/v1/brain-teasers/my-stats

Retrieves brain teaser statistics for the authenticated user.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "stats": {
      "totalAttempts": 25,
      "correctAnswers": 18,
      "totalPoints": 450,
      "averageTime": 32,
      "streakCount": 3,
      "bestStreak": 7,
      "categoryStats": {
        "logic": {
          "attempts": 10,
          "correct": 8,
          "points": 160
        },
        "math": {
          "attempts": 8,
          "correct": 5,
          "points": 100
        },
        "riddle": {
          "attempts": 7,
          "correct": 5,
          "points": 190
        }
      }
    }
  },
  "message": "Brain teaser stats retrieved successfully"
}
```

### POST /api/v1/brain-teasers

Creates a new brain teaser (admin only).

**Request Body:**
```json
{
  "title": "New Brain Teaser",
  "question": "What has keys but no locks?",
  "options": [
    "Piano",
    "Keyboard",
    "Map",
    "All of the above"
  ],
  "correctAnswer": 3,
  "explanation": "A map has keys (legend) but no locks.",
  "difficulty": "easy",
  "category": "riddle",
  "points": 10,
  "timeLimit": 45
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "brainTeaser": {
      "id": "2",
      "title": "New Brain Teaser",
      "question": "What has keys but no locks?",
      "options": [
        "Piano",
        "Keyboard",
        "Map",
        "All of the above"
      ],
      "correctAnswer": 3,
      "explanation": "A map has keys (legend) but no locks.",
      "difficulty": "easy",
      "category": "riddle",
      "points": 10,
      "timeLimit": 45,
      "createdAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Brain teaser created successfully"
}
```

## 💰 Transaction & Wallet Endpoints

### GET /api/v1/transactions

Retrieves transaction history for the authenticated user.

**Query Parameters:**
- type (optional): Filter by transaction type (earning, withdrawal, payout, bonus, referral)
- status (optional): Filter by transaction status (pending, completed, failed, cancelled)
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": "1",
        "userId": "1",
        "type": "earning",
        "amount": 250.00,
        "currency": "NGN",
        "status": "completed",
        "description": "Earning from Ad View",
        "createdAt": "2024-01-20T08:00:00Z",
        "updatedAt": "2024-01-20T08:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Transactions retrieved successfully"
}
```

### GET /api/v1/transactions/{id}

Retrieves a specific transaction by ID.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "transaction": {
      "id": "1",
      "userId": "1",
      "type": "earning",
      "amount": 250.00,
      "currency": "NGN",
      "status": "completed",
      "description": "Earning from Ad View",
      "createdAt": "2024-01-20T08:00:00Z",
      "updatedAt": "2024-01-20T08:00:00Z"
    }
  },
  "message": "Transaction retrieved successfully"
}
```

### GET /api/v1/wallet/summary

Retrieves wallet summary for the authenticated user.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "wallet": {
      "balance": 12500.50,
      "currency": "NGN",
      "totalEarnings": 74349.00,
      "totalWithdrawals": 45600.25
    }
  },
  "message": "Wallet summary retrieved successfully"
}
```

### POST /api/v1/wallet/withdraw

Creates a new withdrawal request.

**Request Body:**
```json
{
  "amount": 5000,
  "payment_method_id": 1
}
```

**Note:** Users must have previously bound their bank account details to their profile before requesting a withdrawal. The system will use the stored bank account information for processing the withdrawal.

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "withdrawal": {
      "id": "100",
      "userId": "1",
      "amount": 5000,
      "currency": "NGN",
      "paymentMethod": {
        "id": 1,
        "name": "Wallet Balance"
      },
      "accountDetails": {
        "accountName": "John Doe",
        "accountNumber": "1234567890",
        "bankName": "First Bank"
      },
      "status": "pending",
      "requestedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Withdrawal request submitted successfully"
}
```

### GET /api/v1/dashboard/referral-stats

Get detailed referral statistics including user information.

**Note:** As of the latest update, referral earnings are only awarded for direct referrals (Level 1). Indirect referral earnings for Level 2 and Level 3 have been disabled.

**Success Response (200):**
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
        "registered_at": "2025-09-04T10:00:00Z"
      },
      {
        "id": 11,
        "username": "referraluser2",
        "package_name": "Basic Package",
        "registered_at": "2025-09-05T10:00:00Z"
      }
    ]
  },
  "message": "Referral statistics retrieved successfully"
}
```

### GET /api/v1/wallet/withdrawals

Retrieves withdrawal history for the authenticated user.

**Query Parameters:**
- status (optional): Filter by status (pending, completed, failed, cancelled)
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "withdrawals": [
      {
        "id": "100",
        "userId": "1",
        "amount": 5000,
        "currency": "NGN",
        "paymentMethod": {
          "type": "bank",
          "name": "Bank Transfer",
          "minAmount": 1000,
          "maxAmount": 100000,
          "processingTime": "1-3 business days",
          "fees": 50
        },
        "accountDetails": {
          "accountName": "John Doe",
          "accountNumber": "1234567890",
          "bankName": "First Bank"
        },
        "status": "pending",
        "requestedAt": "2024-01-20T10:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Withdrawals retrieved successfully"
}
```

### POST /api/v1/wallet/withdrawals/{id}/cancel

Cancels a pending withdrawal request.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "withdrawal": {
      "id": "100",
      "userId": "1",
      "amount": 5000,
      "currency": "NGN",
      "paymentMethod": {
        "type": "bank",
        "name": "Bank Transfer"
      },
      "accountDetails": {
        "accountName": "John Doe",
        "accountNumber": "1234567890",
        "bankName": "First Bank"
      },
      "status": "cancelled",
      "requestedAt": "2024-01-20T10:00:00Z",
      "processedAt": "2024-01-20T11:00:00Z"
    }
  },
  "message": "Withdrawal request cancelled successfully"
}
```

### POST /api/v1/wallet/add-funds

Adds funds to the user's wallet.

**Request Body:**
```json
{
  "amount": 10000,
  "paymentMethod": "card"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "transaction": {
      "id": "600",
      "userId": "1",
      "type": "deposit",
      "amount": 10000,
      "currency": "NGN",
      "status": "completed",
      "description": "Wallet top-up",
      "timestamp": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Funds added successfully"
}
```

### GET /api/v1/transactions/statistics

Retrieves transaction statistics for the authenticated user.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "statistics": {
      "totalEarnings": 74349.00,
      "totalWithdrawals": 45600.25,
      "pendingWithdrawals": 5000.00,
      "monthlyEarnings": 12500.50,
      "topEarningSources": [
        {
          "source": "ad_view",
          "amount": 35000.00
        },
        {
          "source": "referral",
          "amount": 25000.00
        }
      ]
    }
  },
  "message": "Transaction statistics retrieved successfully"
}
```

### POST /api/v1/transactions/export

Exports transaction history to a file.

**Request Body:**
```json
{
  "format": "csv", // or "pdf", "xlsx"
  "startDate": "2024-01-01",
  "endDate": "2024-01-31"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "downloadUrl": "https://api.visionhub.com/storage/exports/transactions_2024_01.csv"
  },
  "message": "Transaction export initiated successfully"
}
```

## 📦 Package Endpoints

### GET /api/v1/packages

Retrieves all available user packages.

**Query Parameters:**
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "packages": [
      {
        "id": "1",
        "name": "Silver Package",
        "price": 5000,
        "benefits": ["Daily earnings up to ₦500", "Referral bonuses", "Access to marketplace"],
        "durationDays": 30,
        "isActive": true,
        "features": ["ad_viewing", "marketplace", "courses"],
        "courseAccessLimit": 5
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Packages retrieved successfully"
}
```

### POST /api/v1/packages

Creates a new user package (admin only).

**Request Body:**
```json
{
  "name": "Gold Package",
  "price": 10000,
  "benefits": ["Daily earnings up to ₦1000", "Referral bonuses", "Access to marketplace", "Premium courses"],
  "durationDays": 30,
  "isActive": true,
  "features": ["ad_viewing", "marketplace", "courses", "premium_courses"],
  "courseAccessLimit": 10
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "package": {
      "id": "2",
      "name": "Gold Package",
      "price": 10000,
      "benefits": ["Daily earnings up to ₦1000", "Referral bonuses", "Access to marketplace", "Premium courses"],
      "durationDays": 30,
      "isActive": true,
      "features": ["ad_viewing", "marketplace", "courses", "premium_courses"],
      "courseAccessLimit": 10,
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Package created successfully"
}
```

### GET /api/v1/packages/{package}

Retrieves a specific user package by ID.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "package": {
      "id": "1",
      "name": "Silver Package",
      "price": 5000,
      "benefits": ["Daily earnings up to ₦500", "Referral bonuses", "Access to marketplace"],
      "durationDays": 30,
      "isActive": true,
      "features": ["ad_viewing", "marketplace", "courses"],
      "courseAccessLimit": 5
    }
  },
  "message": "Package retrieved successfully"
}
```

### PUT /api/v1/packages/{package}

Updates an existing user package.

**Request Body:**
```json
{
  "name": "Updated Silver Package",
  "price": 5500
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "package": {
      "id": "1",
      "name": "Updated Silver Package",
      "price": 5500,
      "benefits": ["Daily earnings up to ₦500", "Referral bonuses", "Access to marketplace"],
      "durationDays": 30,
      "isActive": true,
      "features": ["ad_viewing", "marketplace", "courses"],
      "courseAccessLimit": 5,
      "createdAt": "2024-01-01T00:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Package updated successfully"
}
```

### DELETE /api/v1/packages/{package}

Deletes a user package.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Package deleted successfully"
}
```

## 🏷️ Tag Endpoints

### GET /api/v1/tags

Retrieves all tags.

**Query Parameters:**
- search (optional): Search term for tag name
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tags": [
      {
        "id": "1",
        "name": "web development",
        "slug": "web-development",
        "description": "Web development related content",
        "createdAt": "2024-01-01T00:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Tags retrieved successfully"
}
```

### POST /api/v1/tags

Creates a new tag.

**Request Body:**
```json
{
  "name": "mobile development",
  "description": "Mobile app development related content"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "tag": {
      "id": "2",
      "name": "mobile development",
      "slug": "mobile-development",
      "description": "Mobile app development related content",
      "createdAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Tag created successfully"
}
```

### GET /api/v1/tags/{tag}

Retrieves a specific tag by ID.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tag": {
      "id": "1",
      "name": "web development",
      "slug": "web-development",
      "description": "Web development related content",
      "createdAt": "2024-01-01T00:00:00Z"
    }
  },
  "message": "Tag retrieved successfully"
}
```

### PUT /api/v1/tags/{tag}

Updates an existing tag.

**Request Body:**
```json
{
  "name": "web development updated",
  "description": "Updated web development related content"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tag": {
      "id": "1",
      "name": "web development updated",
      "slug": "web-development-updated",
      "description": "Updated web development related content",
      "createdAt": "2024-01-01T00:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Tag updated successfully"
}
```

### DELETE /api/v1/tags/{tag}

Deletes a tag.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Tag deleted successfully"
}
```

### GET /api/v1/tags-popular

Retrieves popular tags.

**Query Parameters:**
- limit (optional): Number of records to return (default: 10)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tags": [
      {
        "id": "1",
        "name": "web development",
        "usageCount": 125
      }
    ]
  },
  "message": "Popular tags retrieved successfully"
}
```

### GET /api/v1/tags-suggestions

Retrieves tag suggestions based on a search term.

**Query Parameters:**
- q: Search term

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tags": [
      {
        "id": "1",
        "name": "web development",
        "slug": "web-development"
      }
    ]
  },
  "message": "Tag suggestions retrieved successfully"
}
```

### POST /api/v1/tags-bulk

Creates multiple tags in bulk.

**Request Body:**
```json
{
  "tags": [
    {
      "name": "tag1",
      "description": "Description for tag1"
    },
    {
      "name": "tag2",
      "description": "Description for tag2"
    }
  ]
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "tags": [
      {
        "id": "3",
        "name": "tag1",
        "slug": "tag1",
        "description": "Description for tag1",
        "createdAt": "2024-01-20T10:00:00Z"
      },
      {
        "id": "4",
        "name": "tag2",
        "slug": "tag2",
        "description": "Description for tag2",
        "createdAt": "2024-01-20T10:00:00Z"
      }
    ]
  },
  "message": "Tags created successfully"
}
```

## 📁 Project Endpoints

### GET /api/v1/projects

Retrieves all projects for the authenticated user.

**Query Parameters:**
- status (optional): Filter by project status
- search (optional): Search term for project name
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "projects": [
      {
        "id": "1",
        "userId": "1",
        "name": "Website Redesign",
        "description": "Complete redesign of company website",
        "status": "active",
        "settings": {
          "privacy": "private"
        },
        "imagesCount": 5,
        "createdAt": "2024-01-01T00:00:00Z",
        "updatedAt": "2024-01-01T00:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Projects retrieved successfully"
}
```

### POST /api/v1/projects

Creates a new project.

**Request Body:**
```json
{
  "name": "New Project",
  "description": "Description of the new project",
  "settings": {
    "privacy": "private"
  }
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "project": {
      "id": "2",
      "userId": "1",
      "name": "New Project",
      "description": "Description of the new project",
      "status": "active",
      "settings": {
        "privacy": "private"
      },
      "imagesCount": 0,
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Project created successfully"
}
```

### GET /api/v1/projects/{project}

Retrieves a specific project by ID.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "project": {
      "id": "1",
      "userId": "1",
      "name": "Website Redesign",
      "description": "Complete redesign of company website",
      "status": "active",
      "settings": {
        "privacy": "private"
      },
      "images": [
        {
          "id": "1",
          "projectId": "1",
          "name": "Homepage Design",
          "url": "https://api.visionhub.com/storage/projects/1/images/1.jpg",
          "createdAt": "2024-01-01T00:00:00Z"
        }
      ],
      "imagesCount": 5,
      "createdAt": "2024-01-01T00:00:00Z",
      "updatedAt": "2024-01-01T00:00:00Z"
    }
  },
  "message": "Project retrieved successfully"
}
```

### PUT /api/v1/projects/{project}

Updates an existing project.

**Request Body:**
```json
{
  "name": "Updated Project Name",
  "description": "Updated project description"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "project": {
      "id": "1",
      "userId": "1",
      "name": "Updated Project Name",
      "description": "Updated project description",
      "status": "active",
      "settings": {
        "privacy": "private"
      },
      "imagesCount": 5,
      "createdAt": "2024-01-01T00:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Project updated successfully"
}
```

### DELETE /api/v1/projects/{project}

Deletes a project and all associated data.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Project deleted successfully"
}
```

### GET /api/v1/projects/{project}/stats

Retrieves statistics for a specific project.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "stats": {
      "imagesCount": 5,
      "processingJobsCount": 3,
      "storageUsed": "25MB",
      "lastActivity": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Project stats retrieved successfully"
}
```

## 🖼️ Image Endpoints

### GET /api/v1/projects/{project}/images

Retrieves all images for a specific project.

**Query Parameters:**
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "images": [
      {
        "id": "1",
        "projectId": "1",
        "name": "Homepage Design",
        "description": "Homepage design mockup",
        "url": "https://api.visionhub.com/storage/projects/1/images/1.jpg",
        "tags": ["web", "design"],
        "createdAt": "2024-01-01T00:00:00Z",
        "updatedAt": "2024-01-01T00:00:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Images retrieved successfully"
}
```

### POST /api/v1/projects/{project}/images

Uploads a new image to a project.

**Request Body:**
```json
{
  "image": "image_file",
  "name": "New Image",
  "description": "Description of the image",
  "tags": ["web", "design"]
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "image": {
      "id": "2",
      "projectId": "1",
      "name": "New Image",
      "description": "Description of the image",
      "url": "https://api.visionhub.com/storage/projects/1/images/2.jpg",
      "tags": ["web", "design"],
      "createdAt": "2024-01-20T10:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Image uploaded successfully"
}
```

### GET /api/v1/projects/{project}/images/{image}

Retrieves a specific image by ID.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "image": {
      "id": "1",
      "projectId": "1",
      "name": "Homepage Design",
      "description": "Homepage design mockup",
      "url": "https://api.visionhub.com/storage/projects/1/images/1.jpg",
      "tags": ["web", "design"],
      "createdAt": "2024-01-01T00:00:00Z",
      "updatedAt": "2024-01-01T00:00:00Z"
    }
  },
  "message": "Image retrieved successfully"
}
```

### PUT /api/v1/projects/{project}/images/{image}

Updates an existing image.

**Request Body:**
```json
{
  "name": "Updated Image Name",
  "description": "Updated image description",
  "tags": ["web", "design", "updated"]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "image": {
      "id": "1",
      "projectId": "1",
      "name": "Updated Image Name",
      "description": "Updated image description",
      "url": "https://api.visionhub.com/storage/projects/1/images/1.jpg",
      "tags": ["web", "design", "updated"],
      "createdAt": "2024-01-01T00:00:00Z",
      "updatedAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Image updated successfully"
}
```

### DELETE /api/v1/projects/{project}/images/{image}

Deletes an image.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Image deleted successfully"
}
```

### GET /api/v1/projects/{project}/images/{image}/download

Downloads an image file.

**Success Response (200):**
Binary file download

## ⚙️ Processing Job Endpoints

### GET /api/v1/processing-jobs

Retrieves all processing jobs for the authenticated user.

**Query Parameters:**
- status (optional): Filter by job status
- limit (optional): Number of records to return (default: 10)
- page (optional): Page number for pagination (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "processingJobs": [
      {
        "id": "1",
        "userId": "1",
        "imageId": "1",
        "type": "resize",
        "status": "completed",
        "progress": 100,
        "result": {
          "url": "https://api.visionhub.com/storage/projects/1/images/1_resized.jpg"
        },
        "startedAt": "2024-01-01T00:00:00Z",
        "completedAt": "2024-01-01T00:01:00Z"
      }
    ]
  },
  "meta": {
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "message": "Processing jobs retrieved successfully"
}
```

### POST /api/v1/processing-jobs

Creates a new processing job.

**Request Body:**
```json
{
  "imageId": "1",
  "type": "resize",
  "parameters": {
    "width": 800,
    "height": 600
  }
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "processingJob": {
      "id": "2",
      "userId": "1",
      "imageId": "1",
      "type": "resize",
      "status": "queued",
      "progress": 0,
      "parameters": {
        "width": 800,
        "height": 600
      },
      "createdAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Processing job created successfully"
}
```

### GET /api/v1/processing-jobs/{processingJob}

Retrieves a specific processing job by ID.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "processingJob": {
      "id": "1",
      "userId": "1",
      "imageId": "1",
      "type": "resize",
      "status": "completed",
      "progress": 100,
      "parameters": {
        "width": 800,
        "height": 600
      },
      "result": {
        "url": "https://api.visionhub.com/storage/projects/1/images/1_resized.jpg"
      },
      "startedAt": "2024-01-01T00:00:00Z",
      "completedAt": "2024-01-01T00:01:00Z",
      "createdAt": "2024-01-01T00:00:00Z"
    }
  },
  "message": "Processing job retrieved successfully"
}
```

### POST /api/v1/processing-jobs/{processingJob}/cancel

Cancels a processing job.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "processingJob": {
      "id": "2",
      "userId": "1",
      "imageId": "1",
      "type": "resize",
      "status": "cancelled",
      "progress": 0,
      "parameters": {
        "width": 800,
        "height": 600
      },
      "cancelledAt": "2024-01-20T10:01:00Z",
      "createdAt": "2024-01-20T10:00:00Z"
    }
  },
  "message": "Processing job cancelled successfully"
}
```

### GET /api/v1/job-types

Retrieves available job types and their parameters.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "jobTypes": [
      {
        "name": "resize",
        "description": "Resize an image to specific dimensions",
        "parameters": {
          "width": "integer (required)",
          "height": "integer (required)"
        }
      },
      {
        "name": "compress",
        "description": "Compress an image to reduce file size",
        "parameters": {
          "quality": "integer (1-100, required)"
        }
      }
    ]
  },
  "message": "Job types retrieved successfully"
}
```

## 🛡️ Security

- All API requests must be made over HTTPS
- Authentication is handled via Laravel Sanctum tokens
- Passwords are securely hashed using bcrypt
- Input validation is performed on all endpoints
- SQL injection prevention through Eloquent ORM
- Cross-site request forgery (CSRF) protection

---

**Documentation Version**: 1.0  
**Last Updated**: September 2025  
**API Version**: v1