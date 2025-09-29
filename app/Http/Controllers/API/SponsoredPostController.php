<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\SponsoredPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SponsoredPostController extends Controller
{
    /**
     * List all sponsored posts (paginated)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = SponsoredPost::with(['images']);

            // Optional filters
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $total = $query->count();
            $posts = $query->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format sponsored posts data to match documentation
            $formattedPosts = $posts->map(function ($post) {
                // Get image URLs
                $imageUrls = $post->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();
                
                return [
                    'id' => (string)$post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'imageUrl' => $post->image_url,
                    'images' => $imageUrls,
                    'targetUrl' => $post->target_url,
                    'category' => $post->category,
                    'budget' => (int)$post->budget,
                    'spent' => (int)$post->spent,
                    'impressions' => (int)$post->impressions,
                    'clicks' => (int)$post->clicks,
                    'status' => $post->status,
                    'startDate' => $post->start_date->toISOString(),
                    'endDate' => $post->end_date->toISOString(),
                    'createdAt' => $post->created_at->toISOString(),
                    'updatedAt' => $post->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'posts' => $formattedPosts
                ],
                'meta' => [
                    'pagination' => [
                        'total' => $total,
                        'count' => $formattedPosts->count(),
                        'per_page' => (int)$limit,
                        'current_page' => (int)$page,
                        'total_pages' => (int)ceil($total / $limit)
                    ]
                ],
                'message' => 'Sponsored posts retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sponsored posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a single sponsored post
     */
    public function show($id): JsonResponse
    {
        try {
            $post = SponsoredPost::with(['images'])->find($id);
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sponsored post not found'
                ], 404);
            }

            // Get image URLs
            $imageUrls = $post->images->pluck('file_path')->map(function ($path) {
                return Storage::url($path);
            })->toArray();

            // Format sponsored post data to match documentation
            $formattedPost = [
                'id' => (string)$post->id,
                'title' => $post->title,
                'description' => $post->description,
                'imageUrl' => $post->image_url,
                'images' => $imageUrls,
                'targetUrl' => $post->target_url,
                'category' => $post->category,
                'budget' => (int)$post->budget,
                'spent' => (int)$post->spent,
                'impressions' => (int)$post->impressions,
                'clicks' => (int)$post->clicks,
                'status' => $post->status,
                'startDate' => $post->start_date->toISOString(),
                'endDate' => $post->end_date->toISOString(),
                'createdAt' => $post->created_at->toISOString(),
                'updatedAt' => $post->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => ['post' => $formattedPost],
                'message' => 'Sponsored post retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sponsored post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new sponsored post
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'imageUrl' => 'required|url',
                'targetUrl' => 'required|url',
                'category' => 'required|string|max:100',
                'budget' => 'required|numeric|min:0',
                'status' => 'required|in:active,inactive,pending,completed,rejected',
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate',
                'images' => 'sometimes|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            DB::beginTransaction();

            try {
                $post = SponsoredPost::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'image_url' => $request->imageUrl,
                    'target_url' => $request->targetUrl,
                    'category' => $request->category,
                    'budget' => $request->budget,
                    'status' => $request->status,
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                    'user_id' => $user ? $user->id : null,
                    'spent' => 0,
                    'impressions' => 0,
                    'clicks' => 0
                ]);

                // Handle image uploads
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $imageFile) {
                        $path = $imageFile->store('sponsored-post-images', 'public');
                        
                        // Create image record
                        $image = Image::create([
                            'name' => $imageFile->getClientOriginalName(),
                            'original_filename' => $imageFile->getClientOriginalName(),
                            'file_path' => $path,
                            'file_hash' => hash_file('sha256', $imageFile->path()),
                            'mime_type' => $imageFile->getMimeType(),
                            'file_size' => $imageFile->getSize(),
                            'uploaded_by' => $user->id,
                            'status' => 'processed'
                        ]);
                        
                        // Associate image with sponsored post
                        $post->images()->attach($image->id);
                    }
                }

                DB::commit();

                // Get image URLs
                $imageUrls = $post->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();

                // Format sponsored post data to match documentation
                $formattedPost = [
                    'id' => (string)$post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'imageUrl' => $post->image_url,
                    'images' => $imageUrls,
                    'targetUrl' => $post->target_url,
                    'category' => $post->category,
                    'budget' => (int)$post->budget,
                    'spent' => (int)$post->spent,
                    'impressions' => (int)$post->impressions,
                    'clicks' => (int)$post->clicks,
                    'status' => $post->status,
                    'startDate' => $post->start_date->toISOString(),
                    'endDate' => $post->end_date->toISOString(),
                    'createdAt' => $post->created_at->toISOString(),
                    'updatedAt' => $post->updated_at->toISOString()
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Sponsored post created successfully',
                    'data' => ['post' => $formattedPost]
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sponsored post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a sponsored post
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $post = SponsoredPost::find($id);
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sponsored post not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'imageUrl' => 'sometimes|url',
                'targetUrl' => 'sometimes|url',
                'category' => 'sometimes|string|max:100',
                'budget' => 'sometimes|numeric|min:0',
                'status' => 'sometimes|in:active,inactive,pending,completed,rejected',
                'startDate' => 'sometimes|date',
                'endDate' => 'sometimes|date|after_or_equal:startDate',
                'images' => 'sometimes|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Update fields if provided
                $post->title = $request->title ?? $post->title;
                $post->description = $request->description ?? $post->description;
                $post->image_url = $request->imageUrl ?? $post->image_url;
                $post->target_url = $request->targetUrl ?? $post->target_url;
                $post->category = $request->category ?? $post->category;
                $post->budget = $request->budget ?? $post->budget;
                $post->status = $request->status ?? $post->status;
                $post->start_date = $request->startDate ?? $post->start_date;
                $post->end_date = $request->endDate ?? $post->end_date;
                
                $post->save();

                // Handle image uploads
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $imageFile) {
                        $path = $imageFile->store('sponsored-post-images', 'public');
                        
                        // Create image record
                        $image = Image::create([
                            'name' => $imageFile->getClientOriginalName(),
                            'original_filename' => $imageFile->getClientOriginalName(),
                            'file_path' => $path,
                            'file_hash' => hash_file('sha256', $imageFile->path()),
                            'mime_type' => $imageFile->getMimeType(),
                            'file_size' => $imageFile->getSize(),
                            'uploaded_by' => Auth::id(),
                            'status' => 'processed'
                        ]);
                        
                        // Associate image with sponsored post
                        $post->images()->attach($image->id);
                    }
                }

                DB::commit();

                // Get image URLs
                $imageUrls = $post->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();

                // Format sponsored post data to match documentation
                $formattedPost = [
                    'id' => (string)$post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'imageUrl' => $post->image_url,
                    'images' => $imageUrls,
                    'targetUrl' => $post->target_url,
                    'category' => $post->category,
                    'budget' => (int)$post->budget,
                    'spent' => (int)$post->spent,
                    'impressions' => (int)$post->impressions,
                    'clicks' => (int)$post->clicks,
                    'status' => $post->status,
                    'startDate' => $post->start_date->toISOString(),
                    'endDate' => $post->end_date->toISOString(),
                    'createdAt' => $post->created_at->toISOString(),
                    'updatedAt' => $post->updated_at->toISOString()
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Sponsored post updated successfully',
                    'data' => ['post' => $formattedPost]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sponsored post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a sponsored post
     */
    public function destroy($id): JsonResponse
    {
        try {
            $post = SponsoredPost::find($id);
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sponsored post not found'
                ], 404);
            }
            $post->delete();
            return response()->json([
                'success' => true,
                'message' => 'Sponsored post deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sponsored post',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}