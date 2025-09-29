<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    /**
     * Display a listing of tags
     */
    public function index(Request $request): View
    {
        $query = Tag::with(['creator'])
            ->withCount('images');

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('creator', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }

        $tags = $query->orderBy('images_count', 'desc')->paginate(20);

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new tag
     */
    public function create(): View
    {
        return view('admin.tags.create');
    }

    /**
     * Store a newly created tag
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by'] = Auth::id();

        $tag = Tag::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'tag' => $tag->load('creator')
        ], 201);
    }

    /**
     * Display the specified tag
     */
    public function show(Tag $tag): View
    {
        $tag->load(['creator', 'images' => function ($query) {
            $query->with(['project', 'uploader'])->latest()->limit(20);
        }]);

        $stats = [
            'total_images' => $tag->images()->count(),
            'usage_by_project' => $tag->images()
                ->with('project:id,name')
                ->selectRaw('project_id, COUNT(*) as count')
                ->groupBy('project_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'recent_usage' => $tag->images()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];

        return view('admin.tags.show', compact('tag', 'stats'));
    }

    /**
     * Show the form for editing the specified tag
     */
    public function edit(Tag $tag): View
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * Update the specified tag
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully',
            'tag' => $tag->fresh(['creator'])
        ]);
    }

    /**
     * Remove the specified tag
     */
    public function destroy(Tag $tag): JsonResponse
    {
        // Detach from all images first
        $tag->images()->detach();
        
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully'
        ]);
    }

    /**
     * Get tag usage statistics
     */
    public function usage(Tag $tag): JsonResponse
    {
        $usage = [
            'total_images' => $tag->images()->count(),
            'by_project' => $tag->images()
                ->with('project:id,name')
                ->selectRaw('project_id, COUNT(*) as count')
                ->groupBy('project_id')
                ->orderByDesc('count')
                ->get(),
            'by_user' => $tag->images()
                ->with('uploader:id,name')
                ->selectRaw('uploaded_by, COUNT(*) as count')
                ->groupBy('uploaded_by')
                ->orderByDesc('count')
                ->get(),
            'usage_timeline' => $tag->images()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(90))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'recent_images' => $tag->images()
                ->with(['project:id,name', 'uploader:id,name'])
                ->latest()
                ->limit(10)
                ->get(['id', 'name', 'project_id', 'uploaded_by', 'created_at'])
        ];

        return response()->json([
            'success' => true,
            'data' => $usage
        ]);
    }

    /**
     * Merge tags
     */
    public function merge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_tag_id' => 'required|exists:tags,id',
            'target_tag_id' => 'required|exists:tags,id|different:source_tag_id'
        ]);

        $sourceTag = Tag::findOrFail($validated['source_tag_id']);
        $targetTag = Tag::findOrFail($validated['target_tag_id']);

        // Get all images from source tag
        $imageIds = $sourceTag->images()->pluck('images.id');

        // Attach these images to target tag (if not already attached)
        foreach ($imageIds as $imageId) {
            if (!$targetTag->images()->where('images.id', $imageId)->exists()) {
                $targetTag->images()->attach($imageId);
            }
        }

        // Delete the source tag
        $sourceTag->images()->detach();
        $sourceTag->delete();

        return response()->json([
            'success' => true,
            'message' => "Tag '{$sourceTag->name}' merged into '{$targetTag->name}' successfully"
        ]);
    }

    /**
     * Bulk actions on tags
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,change_color,merge',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id',
            'color' => 'required_if:action,change_color|regex:/^#[a-fA-F0-9]{6}$/',
            'target_tag_id' => 'required_if:action,merge|exists:tags,id'
        ]);

        $tags = Tag::whereIn('id', $validated['tag_ids']);

        switch ($validated['action']) {
            case 'delete':
                // Detach from all images first
                foreach ($tags->get() as $tag) {
                    $tag->images()->detach();
                }
                $count = $tags->count();
                $tags->delete();
                $message = "{$count} tags deleted successfully";
                break;

            case 'change_color':
                $count = $tags->update(['color' => $validated['color']]);
                $message = "{$count} tags updated successfully";
                break;

            case 'merge':
                $targetTag = Tag::findOrFail($validated['target_tag_id']);
                $count = 0;
                
                foreach ($tags->get() as $sourceTag) {
                    if ($sourceTag->id !== $targetTag->id) {
                        // Get all images from source tag
                        $imageIds = $sourceTag->images()->pluck('images.id');
                        
                        // Attach to target tag
                        foreach ($imageIds as $imageId) {
                            if (!$targetTag->images()->where('images.id', $imageId)->exists()) {
                                $targetTag->images()->attach($imageId);
                            }
                        }
                        
                        $sourceTag->images()->detach();
                        $sourceTag->delete();
                        $count++;
                    }
                }
                
                $message = "{$count} tags merged successfully";
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}