<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Image;
use App\Models\ProcessingJob;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    /**
     * Get application status
     */
    public function status()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'VisionHub Backend API is running',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
        ]);
    }

    /**
     * Get application info and statistics
     */
    public function info()
    {
        $stats = Cache::remember('api_stats', 300, function () {
            return [
                'total_users' => User::count(),
                'total_projects' => Project::count(),
                'total_images' => Image::count(),
                'total_processing_jobs' => ProcessingJob::count(),
                'total_tags' => Tag::count(),
                'active_projects' => Project::where('status', 'active')->count(),
                'processed_images' => Image::where('status', 'processed')->count(),
                'pending_jobs' => ProcessingJob::where('status', 'pending')->count(),
                'storage_used' => Image::sum('file_size'),
            ];
        });

        return response()->json([
            'app_name' => config('app.name', 'VisionHub'),
            'app_env' => config('app.env', 'production'),
            'app_url' => config('app.url', 'http://localhost'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'statistics' => $stats,
            'features' => [
                'image_processing' => true,
                'object_detection' => true,
                'text_extraction' => true,
                'tag_management' => true,
                'project_organization' => true,
            ],
        ]);
    }

    /**
     * Health check endpoint
     */
    public function health()
    {
        $checks = [
            'app' => 'ok',
            'timestamp' => now()->toISOString()
        ];

        try {
            // Database check
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'error';
        }

        try {
            // Storage check
            \Storage::disk('public')->exists('.');
            $checks['storage'] = 'ok';
        } catch (\Exception $e) {
            $checks['storage'] = 'error';
        }

        $allHealthy = !in_array('error', $checks);

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'checks' => $checks
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Get supported file types and limits
     */
    public function fileTypes()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'supported_image_types' => [
                    'jpeg', 'jpg', 'png', 'gif', 'webp', 'svg'
                ],
                'max_file_size' => '10MB',
                'max_files_per_upload' => 10,
                'max_image_dimensions' => ['width' => 8000, 'height' => 8000],
                'min_image_dimensions' => ['width' => 10, 'height' => 10],
            ]
        ]);
    }

    /**
     * Get maintenance status
     */
    public function maintenance()
    {
        $isDown = app()->isDownForMaintenance();
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_maintenance' => $isDown,
                'message' => $isDown ? 'System is currently under maintenance' : 'System is operational',
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get API documentation structure
     */
    public function documentation()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'base_url' => config('app.url') . '/api',
                'authentication' => 'Bearer Token (Sanctum)',
                'endpoints' => [
                    'auth' => [
                        'POST /register',
                        'POST /login', 
                        'POST /login/email',
                    ],
                    'projects' => [
                        'GET /projects',
                        'POST /projects',
                        'GET /projects/{id}',
                        'PUT /projects/{id}',
                        'DELETE /projects/{id}',
                    ],
                    'images' => [
                        'GET /projects/{project}/images',
                        'POST /projects/{project}/images',
                        'GET /projects/{project}/images/{id}',
                        'PUT /projects/{project}/images/{id}',
                        'DELETE /projects/{project}/images/{id}',
                    ],
                    'processing' => [
                        'GET /processing-jobs',
                        'POST /processing-jobs',
                        'GET /processing-jobs/{id}',
                        'POST /processing-jobs/{id}/cancel',
                    ],
                    'tags' => [
                        'GET /tags',
                        'POST /tags',
                        'GET /tags/{id}',
                        'PUT /tags/{id}',
                        'DELETE /tags/{id}',
                    ],
                ]
            ]
        ]);
    }
}