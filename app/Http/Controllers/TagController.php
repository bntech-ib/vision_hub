<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TagController extends Controller
{
    /**
     * Display a listing of tags
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::with('creator:id,name');
        
        // Search by name if provided
        if ($request->has('search')) {
            $query->search($request->search);
        }
        
        // Filter by creator if provided
        if ($request->has('created_by_me') && $request->created_by_me) {
            $query->where('created_by', Auth::id());
        }

        $tags = $query->withCount('images')
            ->orderBy('name')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * Store a newly created tag
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $validated['created_by'] = Auth::id();

        $tag = Tag::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'data' => $tag->load('creator:id,name'),
        ], 201);
    }

    /**
     * Display the specified tag
     */
    public function show(Tag $tag): JsonResponse
    {
        $tag->load(['creator:id,name', 'images.project']);
        $tag->loadCount('images');

        return response()->json([
            'success' => true,
            'data' => $tag,
        ]);
    }

    /**
     * Update the specified tag
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $this->authorizeTag($tag);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:tags,name,' . $tag->id,
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully',
            'data' => $tag->fresh()->load('creator:id,name'),
        ]);
    }

    /**
     * Remove the specified tag
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorizeTag($tag);

        // Detach tag from all images before deleting
        $tag->images()->detach();
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully',
        ]);
    }

    /**
     * Get popular tags
     */
    public function popular(): JsonResponse
    {
        $tags = Tag::withCount('images')
            ->having('images_count', '>', 0)
            ->orderBy('images_count', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * Get tag suggestions based on search term
     */
    public function suggestions(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $tags = Tag::where('name', 'like', "%{$search}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'color']);

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * Bulk create tags from a list
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tags' => 'required|array|max:20',
            'tags.*' => 'required|string|max:255',
            'default_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $created = [];
        $existing = [];

        foreach ($validated['tags'] as $tagName) {
            $tagName = trim($tagName);
            
            // Check if tag already exists
            $existingTag = Tag::where('name', $tagName)->first();
            
            if ($existingTag) {
                $existing[] = $existingTag;
            } else {
                $created[] = Tag::create([
                    'name' => $tagName,
                    'color' => $validated['default_color'] ?? '#3B82F6',
                    'created_by' => Auth::id(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($created) . ' tags created, ' . count($existing) . ' already existed',
            'data' => [
                'created' => $created,
                'existing' => $existing,
            ],
        ]);
    }

    /**
     * Authorize tag access
     */
    private function authorizeTag(Tag $tag): void
    {
        if ($tag->created_by !== Auth::id()) {
            throw ValidationException::withMessages([
                'tag' => 'You do not have permission to modify this tag.',
            ]);
        }
    }
}