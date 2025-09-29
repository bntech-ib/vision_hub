<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $query = Auth::user()->projects();
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Search by name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $projects = $query->with('images')
            ->withCount(['images', 'processingJobs'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
            
        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }

    /**
     * Store a newly created project
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'settings' => 'nullable|array',
        ]);

        $project = Auth::user()->projects()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'data' => $project->load('images'),
        ], 201);
    }

    /**
     * Display the specified project
     */
    public function show(Project $project): JsonResponse
    {
        $this->authorizeProject($project);

        $project->load([
            'images.tags',
            'images.processingJobs' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($project);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|required|in:active,completed,archived',
            'settings' => 'nullable|array',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'data' => $project->fresh()->load('images'),
        ]);
    }

    /**
     * Remove the specified project
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorizeProject($project);

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
    }

    /**
     * Get project statistics
     */
    public function stats(Project $project): JsonResponse
    {
        $this->authorizeProject($project);

        $stats = [
            'total_images' => $project->images()->count(),
            'processed_images' => $project->images()->where('status', 'processed')->count(),
            'processing_images' => $project->images()->where('status', 'processing')->count(),
            'total_jobs' => $project->processingJobs()->count(),
            'completed_jobs' => $project->processingJobs()->where('status', 'completed')->count(),
            'failed_jobs' => $project->processingJobs()->where('status', 'failed')->count(),
            'total_file_size' => $project->images()->sum('file_size'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Authorize project access
     */
    private function authorizeProject(Project $project): void
    {
        if ($project->user_id !== Auth::id()) {
            throw ValidationException::withMessages([
                'project' => 'You do not have permission to access this project.',
            ]);
        }
    }
}