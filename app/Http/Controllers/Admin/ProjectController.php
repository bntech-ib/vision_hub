<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects
     */
    public function index(Request $request): View
    {
        $query = Project::with(['user', 'images'])
            ->withCount(['images', 'processingJobs']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project
     */
    public function create(): View
    {
        $users = User::select('id', 'name', 'email')->get();
        return view('admin.projects.create', compact('users'));
    }

    /**
     * Store a newly created project
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,completed,archived,suspended',
        ]);

        $project = Project::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'project' => $project
        ]);
    }

    /**
     * Display the specified project
     */
    public function show(Project $project): View
    {
        $project->load([
            'user',
            'images.tags',
            'processingJobs' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        $stats = [
            'total_images' => $project->images()->count(),
            'processed_images' => $project->images()->where('status', 'processed')->count(),
            'total_jobs' => $project->processingJobs()->count(),
            'completed_jobs' => $project->processingJobs()->where('status', 'completed')->count(),
            'failed_jobs' => $project->processingJobs()->where('status', 'failed')->count(),
            'storage_used' => $project->images()->sum('file_size'),
        ];

        return view('admin.projects.show', compact('project', 'stats'));
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit(Project $project): View
    {
        $users = User::select('id', 'name', 'email')->get();
        
        return view('admin.projects.edit', compact('project', 'users'));
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,completed,archived,suspended',
            'settings' => 'nullable|array',
        ]);

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'project' => $project->fresh()
        ]);
    }

    /**
     * Remove the specified project
     */
    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project archived successfully'
        ]);
    }

    /**
     * Suspend a project
     */
    public function suspend(Project $project): JsonResponse
    {
        $project->update(['status' => 'suspended']);

        return response()->json([
            'success' => true,
            'message' => 'Project suspended successfully'
        ]);
    }

    /**
     * Activate a project
     */
    public function activate(Project $project): JsonResponse
    {
        $project->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Project activated successfully'
        ]);
    }

    /**
     * Get project images
     */
    public function images(Project $project): JsonResponse
    {
        $images = $project->images()
            ->with(['tags', 'processingJobs'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    }

    /**
     * Get project processing jobs
     */
    public function jobs(Project $project): JsonResponse
    {
        $jobs = $project->processingJobs()
            ->with(['image', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }

    /**
     * Force delete a project
     */
    public function forceDelete(Project $project): JsonResponse
    {
        $project->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Project permanently deleted'
        ]);
    }

    /**
     * Bulk actions on projects
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:suspend,activate,archive,delete',
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id'
        ]);

        $projects = Project::whereIn('id', $validated['project_ids']);
        
        switch ($validated['action']) {
            case 'suspend':
                $projects->update(['status' => 'suspended']);
                $message = 'Projects suspended successfully';
                break;
            case 'activate':
                $projects->update(['status' => 'active']);
                $message = 'Projects activated successfully';
                break;
            case 'archive':
                $projects->update(['status' => 'archived']);
                $message = 'Projects archived successfully';
                break;
            case 'delete':
                $projects->delete();
                $message = 'Projects deleted successfully';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}