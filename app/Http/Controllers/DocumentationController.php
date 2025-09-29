<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class DocumentationController extends Controller
{
    /**
     * Get comprehensive API documentation
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'api' => [
                    'name' => 'VisionHub Backend API',
                    'version' => '1.0.0',
                    'description' => 'A comprehensive backend API for vision processing, image management, and project organization',
                    'base_url' => config('app.url') . '/api',
                    'authentication' => [
                        'type' => 'Bearer Token (Laravel Sanctum)',
                        'header' => 'Authorization: Bearer {token}',
                        'note' => 'Register or login to get an API token'
                    ]
                ],
                'endpoints' => $this->getEndpoints(),
                'models' => $this->getModels(),
                'examples' => $this->getExamples(),
                'rate_limits' => $this->getRateLimits(),
                'error_codes' => $this->getErrorCodes(),
            ]
        ]);
    }

    /**
     * Get all API endpoints documentation
     */
    private function getEndpoints(): array
    {
        return [
            'authentication' => [
                'POST /register' => [
                    'description' => 'Register a new user account',
                    'parameters' => ['name', 'email', 'password', 'password_confirmation'],
                    'returns' => 'User object with API token'
                ],
                'POST /login' => [
                    'description' => 'Login with email and password',
                    'parameters' => ['email', 'password'],
                    'returns' => 'User object with API token'
                ],
                'POST /logout' => [
                    'description' => 'Logout current session',
                    'auth_required' => true,
                    'returns' => 'Success message'
                ],
                'GET /user' => [
                    'description' => 'Get current user profile',
                    'auth_required' => true,
                    'returns' => 'User object with statistics'
                ],
            ],
            'projects' => [
                'GET /projects' => [
                    'description' => 'Get user projects with optional filtering',
                    'auth_required' => true,
                    'parameters' => ['status?', 'search?', 'per_page?'],
                    'returns' => 'Paginated list of projects'
                ],
                'POST /projects' => [
                    'description' => 'Create a new project',
                    'auth_required' => true,
                    'parameters' => ['name', 'description?', 'settings?'],
                    'returns' => 'Created project object'
                ],
                'GET /projects/{id}' => [
                    'description' => 'Get project details with images',
                    'auth_required' => true,
                    'returns' => 'Project object with related data'
                ],
                'PUT /projects/{id}' => [
                    'description' => 'Update project information',
                    'auth_required' => true,
                    'parameters' => ['name?', 'description?', 'status?', 'settings?'],
                    'returns' => 'Updated project object'
                ],
                'DELETE /projects/{id}' => [
                    'description' => 'Delete project and all associated data',
                    'auth_required' => true,
                    'returns' => 'Success message'
                ],
                'GET /projects/{id}/stats' => [
                    'description' => 'Get project statistics',
                    'auth_required' => true,
                    'returns' => 'Project statistics object'
                ],
            ],
            'images' => [
                'GET /projects/{project}/images' => [
                    'description' => 'Get images for a project',
                    'auth_required' => true,
                    'parameters' => ['status?', 'tags?', 'per_page?'],
                    'returns' => 'Paginated list of images'
                ],
                'POST /projects/{project}/images' => [
                    'description' => 'Upload images to a project',
                    'auth_required' => true,
                    'parameters' => ['images[]', 'names[]?', 'tags[]?'],
                    'returns' => 'Array of uploaded image objects'
                ],
                'GET /projects/{project}/images/{id}' => [
                    'description' => 'Get image details',
                    'auth_required' => true,
                    'returns' => 'Image object with metadata'
                ],
                'PUT /projects/{project}/images/{id}' => [
                    'description' => 'Update image information',
                    'auth_required' => true,
                    'parameters' => ['name?', 'processing_notes?', 'tags[]?'],
                    'returns' => 'Updated image object'
                ],
                'DELETE /projects/{project}/images/{id}' => [
                    'description' => 'Delete image and file',
                    'auth_required' => true,
                    'returns' => 'Success message'
                ],
                'GET /projects/{project}/images/{id}/download' => [
                    'description' => 'Download original image file',
                    'auth_required' => true,
                    'returns' => 'Binary file download'
                ],
            ],
            'processing' => [
                'GET /processing-jobs' => [
                    'description' => 'Get processing jobs with optional filtering',
                    'auth_required' => true,
                    'parameters' => ['status?', 'job_type?', 'project_id?', 'per_page?'],
                    'returns' => 'Paginated list of processing jobs'
                ],
                'POST /processing-jobs' => [
                    'description' => 'Create a new processing job',
                    'auth_required' => true,
                    'parameters' => ['image_id', 'job_type', 'parameters'],
                    'returns' => 'Created processing job object'
                ],
                'GET /processing-jobs/{id}' => [
                    'description' => 'Get processing job details',
                    'auth_required' => true,
                    'returns' => 'Processing job object with results'
                ],
                'POST /processing-jobs/{id}/cancel' => [
                    'description' => 'Cancel a processing job',
                    'auth_required' => true,
                    'returns' => 'Success message'
                ],
                'GET /job-types' => [
                    'description' => 'Get available job types and their parameters',
                    'auth_required' => true,
                    'returns' => 'Job types with parameter schemas'
                ],
            ],
            'tags' => [
                'GET /tags' => [
                    'description' => 'Get tags with optional search',
                    'auth_required' => true,
                    'parameters' => ['search?', 'created_by_me?', 'per_page?'],
                    'returns' => 'Paginated list of tags'
                ],
                'POST /tags' => [
                    'description' => 'Create a new tag',
                    'auth_required' => true,
                    'parameters' => ['name', 'description?', 'color?'],
                    'returns' => 'Created tag object'
                ],
                'PUT /tags/{id}' => [
                    'description' => 'Update tag information',
                    'auth_required' => true,
                    'parameters' => ['name?', 'description?', 'color?'],
                    'returns' => 'Updated tag object'
                ],
                'DELETE /tags/{id}' => [
                    'description' => 'Delete tag',
                    'auth_required' => true,
                    'returns' => 'Success message'
                ],
                'GET /tags-popular' => [
                    'description' => 'Get popular tags by usage count',
                    'auth_required' => true,
                    'returns' => 'Array of popular tags'
                ],
                'GET /tags-suggestions' => [
                    'description' => 'Get tag suggestions based on search',
                    'auth_required' => true,
                    'parameters' => ['q'],
                    'returns' => 'Array of suggested tags'
                ],
                'POST /tags-bulk' => [
                    'description' => 'Bulk create tags from array',
                    'auth_required' => true,
                    'parameters' => ['tags[]', 'default_color?'],
                    'returns' => 'Created and existing tags arrays'
                ],
            ],
        ];
    }

    /**
     * Get data models documentation
     */
    private function getModels(): array
    {
        return [
            'User' => [
                'fields' => ['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'],
                'relationships' => ['projects', 'images', 'processingJobs', 'tags']
            ],
            'Project' => [
                'fields' => ['id', 'name', 'description', 'user_id', 'status', 'settings', 'completed_at', 'created_at', 'updated_at'],
                'relationships' => ['user', 'images', 'processingJobs'],
                'status_values' => ['active', 'completed', 'archived']
            ],
            'Image' => [
                'fields' => ['id', 'name', 'original_filename', 'file_path', 'file_hash', 'mime_type', 'file_size', 'width', 'height', 'project_id', 'uploaded_by', 'metadata', 'status', 'processing_notes', 'created_at', 'updated_at'],
                'relationships' => ['project', 'uploader', 'processingJobs', 'tags'],
                'status_values' => ['uploaded', 'processing', 'processed', 'error']
            ],
            'ProcessingJob' => [
                'fields' => ['id', 'job_id', 'image_id', 'user_id', 'job_type', 'parameters', 'status', 'result', 'error_message', 'started_at', 'completed_at', 'progress', 'created_at', 'updated_at'],
                'relationships' => ['image', 'user'],
                'status_values' => ['pending', 'processing', 'completed', 'failed']
            ],
            'Tag' => [
                'fields' => ['id', 'name', 'slug', 'description', 'color', 'created_by', 'created_at', 'updated_at'],
                'relationships' => ['creator', 'images']
            ],
        ];
    }

    /**
     * Get API usage examples
     */
    private function getExamples(): array
    {
        return [
            'authentication' => [
                'register' => [
                    'url' => 'POST /api/register',
                    'body' => [
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                        'password' => 'password123',
                        'password_confirmation' => 'password123'
                    ],
                    'response' => [
                        'success' => true,
                        'message' => 'User registered successfully',
                        'data' => [
                            'user' => ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
                            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                            'token_type' => 'Bearer'
                        ]
                    ]
                ],
                'login' => [
                    'url' => 'POST /api/login',
                    'body' => [
                        'email' => 'john@example.com',
                        'password' => 'password123'
                    ]
                ]
            ],
            'project_creation' => [
                'url' => 'POST /api/projects',
                'headers' => ['Authorization: Bearer {token}'],
                'body' => [
                    'name' => 'My Photography Project',
                    'description' => 'Collection of nature photographs',
                    'settings' => ['auto_tag' => true, 'quality' => 'high']
                ]
            ],
            'image_upload' => [
                'url' => 'POST /api/projects/1/images',
                'headers' => ['Authorization: Bearer {token}', 'Content-Type: multipart/form-data'],
                'body' => [
                    'images[]' => '[binary file data]',
                    'names[]' => 'Beautiful sunset',
                    'tags[]' => [1, 2, 3]
                ]
            ]
        ];
    }

    /**
     * Get rate limiting information
     */
    private function getRateLimits(): array
    {
        return [
            'general_api' => '100 requests per minute per user/IP',
            'authentication' => '10 requests per minute per IP',
            'file_uploads' => '20 requests per minute per user/IP',
            'headers' => [
                'X-RateLimit-Limit' => 'Maximum requests allowed',
                'X-RateLimit-Remaining' => 'Remaining requests in current window',
                'Retry-After' => 'Seconds until rate limit resets (when limit exceeded)'
            ]
        ];
    }

    /**
     * Get error codes documentation
     */
    private function getErrorCodes(): array
    {
        return [
            '200' => 'OK - Request successful',
            '201' => 'Created - Resource created successfully',
            '400' => 'Bad Request - Invalid request parameters',
            '401' => 'Unauthorized - Authentication required',
            '403' => 'Forbidden - Access denied',
            '404' => 'Not Found - Resource not found',
            '422' => 'Unprocessable Entity - Validation errors',
            '429' => 'Too Many Requests - Rate limit exceeded',
            '500' => 'Internal Server Error - Server error occurred',
        ];
    }
}