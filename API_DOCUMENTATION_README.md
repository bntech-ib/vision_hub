# VisionHub API Documentation

## Documentation Files

This project contains the following API documentation files:

1. **[VISIONHUB_API_V2_DOCUMENTATION.md](VISIONHUB_API_V2_DOCUMENTATION.md)** - **Primary API Documentation**
   - Complete and up-to-date documentation for all API endpoints
   - Includes request/response formats, error handling, and implementation details
   - Contains all public and protected endpoints
   - Comprehensive error codes and meanings
   - Rate limiting and data type information

2. **[API-DOCUMENTATION.md](API-DOCUMENTATION.md)** - **Deprecated**
   - Original API documentation (now deprecated)
   - Points to the V2 documentation for current information

3. **[ADMIN-API-DOCUMENTATION.md](ADMIN-API-DOCUMENTATION.md)** - **Deprecated**
   - Original Admin API documentation (now deprecated)
   - Points to the V2 documentation for current information

## Using the API Documentation

For the most current and complete API documentation, please refer to:
- **[VISIONHUB_API_V2_DOCUMENTATION.md](VISIONHUB_API_V2_DOCUMENTATION.md)**

This document includes:
- All available API endpoints
- Detailed request/response examples
- Error handling information
- Authentication requirements
- Rate limiting policies
- Data type specifications
- Versioning information

## API Endpoints Covered

The V2 documentation covers all major functionality including:

- User authentication and management
- Advertisement interactions and management
- Transaction and wallet operations
- Product and course management
- Brain teaser interactions
- Dashboard statistics
- Profile and bank account management
- Support options
- System administration (admin-only endpoints)

## Implementation Notes

All API endpoints follow RESTful principles and return JSON responses with a consistent structure:

```json
{
  "success": true,
  "data": {},
  "message": "Descriptive message"
}
```

Error responses follow a similar structure:

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error information"
}
```

## Authentication

Most endpoints require Bearer token authentication:

```
Authorization: Bearer <your-token>
```

## Rate Limiting

API endpoints are rate-limited:
- Authentication endpoints: 10 requests per minute
- All other endpoints: 60 requests per minute

## Support

For questions about the API, please refer to the V2 documentation or contact the development team.