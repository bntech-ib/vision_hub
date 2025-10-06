<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Get API information
     */
    public function info(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'name' => 'VisionHub API',
                'version' => '1.0.0',
                'description' => 'VisionHub Platform API',
                'documentation_url' => url('/api/v1/docs')
            ],
            'message' => 'API information retrieved successfully'
        ]);
    }

    /**
     * Health check endpoint
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'status' => 'healthy',
                'timestamp' => now()->toISOString()
            ],
            'message' => 'Service is healthy'
        ]);
    }

    /**
     * Get supported file types
     */
    public function fileTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'image' => ['jpeg', 'jpg', 'png', 'gif', 'svg'],
                'document' => ['pdf', 'doc', 'docx', 'txt'],
                'video' => ['mp4', 'avi', 'mov'],
                'audio' => ['mp3', 'wav', 'ogg']
            ],
            'message' => 'Supported file types retrieved successfully'
        ]);
    }

    /**
     * Check maintenance status
     */
    public function maintenance(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'maintenance_mode' => app()->isDownForMaintenance(),
                'scheduled_maintenance' => false,
                'maintenance_message' => app()->isDownForMaintenance() ? 'System is currently under maintenance' : 'System is operational'
            ],
            'message' => 'Maintenance status retrieved successfully'
        ]);
    }
}