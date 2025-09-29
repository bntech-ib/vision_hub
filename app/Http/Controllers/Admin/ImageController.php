<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    /**
     * Display a listing of images
     */
    public function index(Request $request): View
    {
        $query = Image::with(['project.user', 'uploader', 'tags', 'processingJobs'])
            ->withCount('processingJobs');

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('original_filename', 'like', '%' . $request->search . '%')
                  ->orWhereHas('project', function ($projectQuery) use ($request) {
                      $projectQuery->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('uploader', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('mime_type')) {
            $query->where('mime_type', 'like', $request->mime_type . '%');
        }

        // Size filters
        if ($request->filled('min_size')) {
            $query->where('file_size', '>=', $request->min_size * 1024 * 1024); // Convert MB to bytes
        }

        if ($request->filled('max_size')) {
            $query->where('file_size', '<=', $request->max_size * 1024 * 1024);
        }

        $images = $query->orderBy('created_at', 'desc')->paginate(20);
        $projects = Project::select('id', 'name')->get();

        return view('admin.images.index', compact('images', 'projects'));
    }

    /**
     * Display the specified image
     */
    public function show(Image $image): View
    {
        $image->load([
            'project.user',
            'uploader',
            'tags',
            'processingJobs' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return view('admin.images.show', compact('image'));
    }

    /**
     * Show the form for editing the specified image
     */
    public function edit(Image $image): View
    {
        $image->load(['project', 'tags']);
        $projects = Project::select('id', 'name')->get();

        return view('admin.images.edit', compact('image', 'projects'));
    }

    /**
     * Update the specified image
     */
    public function update(Request $request, Image $image): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:uploaded,processing,processed,error,flagged',
            'processing_notes' => 'nullable|string|max:1000',
            'project_id' => 'required|exists:projects,id',
        ]);

        $image->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully',
            'image' => $image->fresh(['project', 'uploader', 'tags'])
        ]);
    }

    /**
     * Remove the specified image
     */
    public function destroy(Image $image): JsonResponse
    {
        // Delete the physical file
        if (Storage::exists($image->file_path)) {
            Storage::delete($image->file_path);
        }

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }

    /**
     * Download the image
     */
    public function download(Image $image): StreamedResponse
    {
        if (!Storage::exists($image->file_path)) {
            abort(404, 'Image file not found');
        }

        return Storage::download($image->file_path, $image->original_filename);
    }

    /**
     * Moderate an image
     */
    public function moderate(Request $request, Image $image): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:processed,flagged,error',
            'notes' => 'nullable|string|max:500'
        ]);

        $image->update([
            'status' => $validated['status'],
            'processing_notes' => $validated['notes']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image moderated successfully'
        ]);
    }

    /**
     * Force delete an image
     */
    public function forceDelete(Image $image): JsonResponse
    {
        // Delete the physical file
        if (Storage::exists($image->file_path)) {
            Storage::delete($image->file_path);
        }

        $image->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Image permanently deleted'
        ]);
    }

    /**
     * Get flagged images
     */
    public function flagged(Request $request): JsonResponse
    {
        $images = Image::where('status', 'flagged')
            ->with(['project.user', 'uploader'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    }

    /**
     * Bulk actions on images
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,flag,delete,change_status',
            'image_ids' => 'required|array',
            'image_ids.*' => 'exists:images,id',
            'status' => 'required_if:action,change_status|in:uploaded,processing,processed,error,flagged'
        ]);

        $images = Image::whereIn('id', $validated['image_ids']);

        switch ($validated['action']) {
            case 'approve':
                $images->update(['status' => 'processed']);
                $message = 'Images approved successfully';
                break;
            case 'flag':
                $images->update(['status' => 'flagged']);
                $message = 'Images flagged successfully';
                break;
            case 'change_status':
                $images->update(['status' => $validated['status']]);
                $message = 'Image status updated successfully';
                break;
            case 'delete':
                // Delete physical files
                $imageRecords = $images->get();
                foreach ($imageRecords as $image) {
                    if (Storage::exists($image->file_path)) {
                        Storage::delete($image->file_path);
                    }
                }
                $images->delete();
                $message = 'Images deleted successfully';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Storage analysis
     */
    public function storageAnalysis(): JsonResponse
    {
        $analysis = [
            'total_images' => Image::count(),
            'total_storage' => Image::sum('file_size'),
            'by_type' => Image::selectRaw('mime_type, COUNT(*) as count, SUM(file_size) as total_size')
                ->groupBy('mime_type')
                ->get(),
            'by_status' => Image::selectRaw('status, COUNT(*) as count, SUM(file_size) as total_size')
                ->groupBy('status')
                ->get(),
            'by_project' => Image::with('project:id,name')
                ->selectRaw('project_id, COUNT(*) as count, SUM(file_size) as total_size')
                ->groupBy('project_id')
                ->orderByDesc('total_size')
                ->limit(10)
                ->get(),
            'largest_images' => Image::with(['project:id,name', 'uploader:id,name'])
                ->orderByDesc('file_size')
                ->limit(10)
                ->get(['id', 'name', 'file_size', 'project_id', 'uploaded_by', 'created_at'])
        ];

        return response()->json([
            'success' => true,
            'data' => $analysis
        ]);
    }

    /**
     * Quick flag an image
     */
    public function quickFlag(Request $request, Image $image): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        $image->update([
            'status' => 'flagged',
            'processing_notes' => $validated['reason'] ?? 'Flagged by admin'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image flagged successfully'
        ]);
    }

    /**
     * Search images
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        $images = Image::with(['project:id,name', 'uploader:id,name'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('original_filename', 'like', '%' . $query . '%')
                  ->orWhereHas('project', function ($projectQuery) use ($query) {
                      $projectQuery->where('name', 'like', '%' . $query . '%');
                  });
            })
            ->limit(10)
            ->get(['id', 'name', 'original_filename', 'status', 'project_id', 'uploaded_by', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    }
}