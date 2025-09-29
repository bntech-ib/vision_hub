# VisionHub Admin Dashboard API Documentation

## Overview
This document provides a comprehensive guide to the VisionHub Admin Dashboard API endpoints, detailing their functionality, expected parameters, and response formats. The API is built with Laravel and follows RESTful principles, using JSON for data exchange.

## Base URL
- Development: `http://localhost:8000/admin/api`
- Production: `https://yourdomain.com/admin/api`

## Authentication
All Admin API requests require authentication via session cookies and must be made from an authenticated admin session:

```
Content-Type: application/json
Accept: application/json
X-Requested-With: XMLHttpRequest
```

## API Response Format

### Success Response
```json
{
  "success": true,
  "data": {},
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

## Admin Dashboard API Endpoints

### Dashboard Analytics

#### GET /admin/api/stats/overview
Retrieves overview statistics for the admin dashboard.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "total_users": 150,
    "total_ads": 45,
    "total_products": 23,
    "total_courses": 12,
    "total_brain_teasers": 8,
    "total_transactions": 89,
    "total_revenue": 25600.50,
    "pending_withdrawals": 5,
    "active_ads": 32,
    "published_courses": 9,
    "users_with_packages": 67,
    "monthly_revenue": 8450.25,
    "total_sponsored_posts": 15,
    "active_sponsored_posts": 12,
    "pending_transactions": 7,
    "completed_withdrawals": 23,
    "active_brain_teasers": 5
  },
  "message": "Dashboard stats retrieved successfully"
}
```

#### GET /admin/api/stats/charts
Retrieves chart data for the admin dashboard.

**Query Parameters:**
- period (optional): Number of days for the chart data (default: 30)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "labels": ["Jan 01", "Jan 02", "Jan 03", "..."],
    "values": [1200, 1500, 980, "..."]
  },
  "message": "Chart data retrieved successfully"
}
```

#### GET /admin/api/stats/recent-activity
Retrieves recent activity data for the admin dashboard.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "recent_users": [...],
    "recent_transactions": [...]
  },
  "message": "Recent activity data retrieved successfully"
}
```

### User Management

#### GET /admin/api/search/users
Search for users by name, email, or username.

**Query Parameters:**
- q: Search query string

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  ]
}
```

#### POST /admin/api/users/{user}/quick-suspend
Quickly suspend a user account.

**Success Response (200):**
```json
{
  "success": true,
  "message": "User suspended successfully"
}
```

### Project Management

#### GET /admin/api/search/projects
Search for projects by name or description.

**Query Parameters:**
- q: Search query string

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Project Name",
      "user_id": 5
    }
  ]
}
```

#### POST /admin/api/projects/{project}/quick-moderate
Quickly moderate a project.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Project moderated successfully"
}
```

### Image Management

#### GET /admin/api/search/images
Search for images by name or description.

**Query Parameters:**
- q: Search query string

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Image Name",
      "project_id": 3
    }
  ]
}
```

#### POST /admin/api/images/{image}/quick-flag
Quickly flag an image.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Image flagged successfully"
}
```

### System Management

#### GET /admin/api/notifications
Get system notifications.

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "System Notification",
      "message": "Notification message",
      "read": false,
      "created_at": "2024-01-20T10:00:00Z"
    }
  ],
  "message": "Notifications retrieved successfully"
}
```

#### PUT /admin/api/notifications/{notification}/read
Mark a notification as read.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

#### POST /admin/api/notifications/mark-all-read
Mark all notifications as read.

**Success Response (200):**
```json
{
  "success": true,
  "message": "All notifications marked as read"
}
```

### Two-Factor Authentication

#### GET /admin/api/2fa/status
Get 2FA status for the authenticated admin.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "enabled": true
  },
  "message": "2FA status retrieved successfully"
}
```

#### POST /admin/api/2fa/enable
Enable 2FA for the authenticated admin.

**Success Response (200):**
```json
{
  "success": true,
  "message": "2FA enabled successfully"
}
```

#### POST /admin/api/2fa/confirm
Confirm 2FA setup with a verification code.

**Request Body:**
```json
{
  "code": "123456"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "2FA confirmed successfully"
}
```

#### POST /admin/api/2fa/disable
Disable 2FA for the authenticated admin.

**Request Body:**
```json
{
  "password": "current_password"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "2FA disabled successfully"
}
```

#### POST /admin/api/2fa/generate-recovery-codes
Generate new recovery codes for 2FA.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "recovery_codes": ["code1", "code2", "..."]
  },
  "message": "Recovery codes generated successfully"
}
```

### Security Logs

#### GET /admin/api/security-logs
Get security logs for the authenticated admin.

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "action": "login",
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2024-01-20T10:00:00Z"
    }
  ],
  "message": "Security logs retrieved successfully"
}
```

## System Management API Endpoints

### System Information

#### GET /admin/system
Get system overview information.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "php_version": "8.1.0",
    "laravel_version": "9.0.0",
    "server_time": "2024-01-20 10:00:00",
    "timezone": "UTC",
    "environment": "production",
    "debug_mode": false,
    "memory_usage": "32 MB",
    "memory_peak": "48 MB",
    "disk_usage": {
      "total": "100 GB",
      "used": "25 GB",
      "free": "75 GB",
      "percentage": 25
    },
    "uptime": "10 days",
    "cache_driver": "redis",
    "session_driver": "database",
    "queue_driver": "redis",
    "mail_driver": "smtp",
    "filesystem_driver": "local"
  },
  "message": "System information retrieved successfully"
}
```

### Logs Management

#### GET /admin/system/logs
Get system logs.

**Query Parameters:**
- level (optional): Filter by log level (emergency, alert, critical, error, warning, notice, info, debug)
- limit (optional): Number of log entries to return (10-1000, default: 100)
- date (optional): Filter by date (YYYY-MM-DD)

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "level": "error",
      "message": "Error message",
      "context": {},
      "datetime": "2024-01-20T10:00:00Z"
    }
  ],
  "message": "Logs retrieved successfully"
}
```

### Cache Management

#### GET /admin/system/cache
Get cache information.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "cache_driver": "redis",
    "session_driver": "database",
    "queue_driver": "redis",
    "cache_size": "10 MB",
    "cache_keys_count": 1250
  },
  "message": "Cache information retrieved successfully"
}
```

#### POST /admin/system/cache/clear
Clear application cache.

**Request Body:**
```json
{
  "types": ["config", "route", "view", "cache", "compiled"]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Cache operations completed",
  "results": [
    "Configuration cache cleared",
    "Route cache cleared",
    "View cache cleared",
    "Application cache cleared",
    "Compiled files cleared"
  ]
}
```

### Queue Management

#### GET /admin/system/queue
Get queue information.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "driver": "redis",
    "failed_jobs": 0,
    "pending_jobs": 5,
    "processed_today": 120,
    "workers_active": 2
  },
  "message": "Queue information retrieved successfully"
}
```

#### POST /admin/system/queue/restart
Restart queue workers.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Queue workers restart signal sent"
}
```

### Maintenance Mode

#### GET /admin/system/maintenance
Get maintenance mode status.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "is_down": false,
    "status": "up"
  },
  "message": "Maintenance mode status retrieved successfully"
}
```

#### POST /admin/system/maintenance/enable
Enable maintenance mode.

**Request Body:**
```json
{
  "message": "System is down for maintenance",
  "allow": ["192.168.1.1", "10.0.0.1"]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Maintenance mode enabled"
}
```

#### POST /admin/system/maintenance/disable
Disable maintenance mode.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Maintenance mode disabled"
}
```

### Backup Management

#### GET /admin/system/backup
Get backup information.

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "backup_database_20240120_100000.zip",
      "name": "backup_database_20240120_100000.zip",
      "date": "2024-01-20 10:00:00",
      "type": "database",
      "size": "5 MB"
    }
  ],
  "message": "Backups retrieved successfully"
}
```

#### POST /admin/system/backup/create
Create a new backup.

**Request Body:**
```json
{
  "type": "database" // or "files" or "full"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Database backup created successfully",
  "backup_id": "backup_database_20240120_100000.zip"
}
```

#### GET /admin/system/backup/download/{filename}
Download a backup file.

**Success Response (200):**
Binary file download

#### POST /admin/system/backup/delete
Delete a backup file.

**Request Body:**
```json
{
  "filename": "backup_database_20240120_100000.zip"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Backup deleted successfully"
}
```

### Storage Management

#### GET /admin/system/storage
Get storage information.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "default_disk": "local",
    "disks": {
      "local": {
        "driver": "local",
        "root": "/var/www/storage/app",
        "total_space": "100 GB",
        "used_space": "25 GB",
        "free_space": "75 GB"
      }
    },
    "temp_files": 15,
    "log_files_size": "2.5 MB"
  },
  "message": "Storage information retrieved successfully"
}
```

#### POST /admin/system/storage/cleanup
Clean up storage.

**Request Body:**
```json
{
  "types": ["temp", "logs", "cache", "old_backups"],
  "older_than_days": 7
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Storage cleanup completed",
  "results": [
    "Cleaned up 15 temporary files",
    "Cleaned up 2.5 MB of old logs",
    "Cache cleaned up",
    "Cleaned up 2 old backups"
  ]
}
```

## Error Handling

The API uses standard HTTP status codes to indicate the success or failure of requests:

- 200: OK - Request successful
- 400: Bad Request - Invalid request parameters
- 401: Unauthorized - Authentication required
- 403: Forbidden - Insufficient permissions
- 404: Not Found - Resource not found
- 422: Unprocessable Entity - Validation errors
- 500: Internal Server Error - Server error

## Rate Limiting

To prevent abuse, the API implements rate limiting:
- 60 requests per minute per IP address
- 1000 requests per hour per authenticated user

When rate limits are exceeded, the API returns a 429 (Too Many Requests) status code.

## Security

- All API requests must be made over HTTPS
- Authentication is handled via Laravel session authentication
- Input validation is performed on all endpoints
- SQL injection prevention through Eloquent ORM
- Cross-site request forgery (CSRF) protection