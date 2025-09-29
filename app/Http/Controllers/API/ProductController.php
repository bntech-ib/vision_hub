<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Get all products
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['seller', 'images'])
                ->where('status', 'active');

            // Apply filters
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            if ($request->has('minPrice') && $request->minPrice !== null) {
                $query->where('price', '>=', $request->minPrice);
            }

            if ($request->has('maxPrice') && $request->maxPrice !== null) {
                $query->where('price', '<=', $request->maxPrice);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if (in_array($sortBy, ['name', 'price', 'created_at', 'rating'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $total = $query->count();
            $products = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format products data
            $formattedProducts = $products->map(function ($product) {
                // Get image URLs
                $imageUrls = $product->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();
                
                // Get full image objects with metadata
                $imageObjects = $product->images->map(function ($image) {
                    return [
                        'id' => (string)$image->id,
                        'name' => $image->name,
                        'originalFilename' => $image->original_filename,
                        'url' => Storage::url($image->file_path),
                        'mimeType' => $image->mime_type,
                        'fileSize' => (int)$image->file_size,
                        'width' => $image->width,
                        'height' => $image->height,
                        'formattedSize' => $image->formatted_size,
                        'createdAt' => $image->created_at->toISOString(),
                        'updatedAt' => $image->updated_at->toISOString()
                    ];
                })->toArray();
                
                return [
                    'id' => (string)$product->id,
                    'sellerId' => (string)$product->seller_id,
                    'seller' => [
                        'id' => (string)$product->seller->id,
                        'username' => $product->seller->username,
                        'name' => $product->seller->name,
                        'country' => $product->seller->country,
                    ],
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (int)$product->price,
                    'currency' => 'NGN',
                    'category' => $product->category,
                    'images' => $imageObjects, // Include full image objects with metadata
                    'stockQuantity' => (int)$product->stock_quantity,
                    'specifications' => $product->specifications,
                    'status' => $product->status,
                    'rating' => $product->rating ? (float)$product->rating : null,
                    'totalReviews' => (int)$product->total_reviews,
                    'isFeatured' => (bool)$product->is_featured,
                    'viewCount' => (int)$product->view_count,
                    'createdAt' => $product->created_at->toISOString(),
                    'updatedAt' => $product->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $formattedProducts
                ],
                'meta' => [
                    'pagination' => [
                        'total' => $total,
                        'count' => $formattedProducts->count(),
                        'per_page' => (int)$limit,
                        'current_page' => (int)$page,
                        'total_pages' => (int)ceil($total / $limit)
                    ]
                ],
                'message' => 'Products retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product categories
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = Product::select('category')
                ->where('status', 'active')
                ->groupBy('category')
                ->pluck('category')
                ->filter()
                ->values()
                ->map(function ($category) {
                    return [
                        'value' => $category,
                        'label' => ucfirst($category)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => ['categories' => $categories],
                'message' => 'Categories retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single product details
     */
    public function show($id): JsonResponse
    {
        try {
            $product = Product::with(['seller', 'images'])->find($id);

            if (!$product || $product->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Increment view count
            $product->increment('view_count');

            // Get image URLs
            $imageUrls = $product->images->pluck('file_path')->map(function ($path) {
                return Storage::url($path);
            })->toArray();

            // Get full image objects with metadata
            $imageObjects = $product->images->map(function ($image) {
                return [
                    'id' => (string)$image->id,
                    'name' => $image->name,
                    'originalFilename' => $image->original_filename,
                    'url' => Storage::url($image->file_path),
                    'mimeType' => $image->mime_type,
                    'fileSize' => (int)$image->file_size,
                    'width' => $image->width,
                    'height' => $image->height,
                    'formattedSize' => $image->formatted_size,
                    'createdAt' => $image->created_at->toISOString(),
                    'updatedAt' => $image->updated_at->toISOString()
                ];
            })->toArray();

            // Format product data
            $formattedProduct = [
                'id' => (string)$product->id,
                'sellerId' => (string)$product->seller_id,
                'seller' => [
                    'id' => (string)$product->seller->id,
                    'username' => $product->seller->username,
                    'name' => $product->seller->name,
                    'country' => $product->seller->country,
                ],
                'name' => $product->name,
                'description' => $product->description,
                'price' => (int)$product->price,
                'currency' => 'NGN',
                'category' => $product->category,
                'images' => $imageObjects, // Include full image objects with metadata
                'stockQuantity' => (int)$product->stock_quantity,
                'specifications' => $product->specifications,
                'status' => $product->status,
                'rating' => $product->rating ? (float)$product->rating : null,
                'totalReviews' => (int)$product->total_reviews,
                'isFeatured' => (bool)$product->is_featured,
                'viewCount' => (int)$product->view_count,
                'createdAt' => $product->created_at->toISOString(),
                'updatedAt' => $product->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => ['product' => $formattedProduct],
                'message' => 'Product retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new product (for sellers)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'stockQuantity' => 'required|integer|min:0',
                'specifications' => 'nullable|array',
                'images' => 'required|array|min:1',
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
                // Create the product
                $product = Product::create([
                    'seller_id' => $user->id,
                    'name' => $request->name,
                    'description' => $request->description,
                    'category' => $request->category,
                    'price' => $request->price,
                    'stock_quantity' => $request->stockQuantity,
                    'specifications' => $request->specifications,
                    'status' => 'active',
                    'is_featured' => false,
                    'view_count' => 0
                ]);

                // Handle image uploads
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $imageFile) {
                        $path = $imageFile->store('product-images', 'public');
                        
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
                        
                        // Associate image with product
                        $product->images()->attach($image->id);
                    }
                }

                DB::commit();

                // Load relationships
                $product->load(['seller', 'images']);

                // Get image URLs
                $imageUrls = $product->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();

                // Get full image objects with metadata
                $imageObjects = $product->images->map(function ($image) {
                    return [
                        'id' => (string)$image->id,
                        'name' => $image->name,
                        'originalFilename' => $image->original_filename,
                        'url' => Storage::url($image->file_path),
                        'mimeType' => $image->mime_type,
                        'fileSize' => (int)$image->file_size,
                        'width' => $image->width,
                        'height' => $image->height,
                        'formattedSize' => $image->formatted_size,
                        'createdAt' => $image->created_at->toISOString(),
                        'updatedAt' => $image->updated_at->toISOString()
                    ];
                })->toArray();

                // Format product data
                $formattedProduct = [
                    'id' => (string)$product->id,
                    'sellerId' => (string)$product->seller_id,
                    'seller' => [
                        'id' => (string)$product->seller->id,
                        'username' => $product->seller->username,
                        'name' => $product->seller->name,
                        'country' => $product->seller->country,
                    ],
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (int)$product->price,
                    'currency' => 'NGN',
                    'category' => $product->category,
                    'images' => $imageObjects, // Include full image objects with metadata
                    'stockQuantity' => (int)$product->stock_quantity,
                    'specifications' => $product->specifications,
                    'status' => $product->status,
                    'rating' => $product->rating ? (float)$product->rating : null,
                    'totalReviews' => (int)$product->total_reviews,
                    'isFeatured' => (bool)$product->is_featured,
                    'viewCount' => (int)$product->view_count,
                    'createdAt' => $product->created_at->toISOString(),
                    'updatedAt' => $product->updated_at->toISOString()
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully',
                    'data' => ['product' => $formattedProduct]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get seller's products
     */
    public function myProducts(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Product::with(['images'])->where('seller_id', $user->id)
                ->orderBy('created_at', 'desc');

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $total = $query->count();
            $products = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format products data
            $formattedProducts = $products->map(function ($product) {
                // Get image URLs
                $imageUrls = $product->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();
                
                // Get full image objects with metadata
                $imageObjects = $product->images->map(function ($image) {
                    return [
                        'id' => (string)$image->id,
                        'name' => $image->name,
                        'originalFilename' => $image->original_filename,
                        'url' => Storage::url($image->file_path),
                        'mimeType' => $image->mime_type,
                        'fileSize' => (int)$image->file_size,
                        'width' => $image->width,
                        'height' => $image->height,
                        'formattedSize' => $image->formatted_size,
                        'createdAt' => $image->created_at->toISOString(),
                        'updatedAt' => $image->updated_at->toISOString()
                    ];
                })->toArray();
                
                return [
                    'id' => (string)$product->id,
                    'sellerId' => (string)$product->seller_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (int)$product->price,
                    'currency' => 'NGN',
                    'category' => $product->category,
                    'images' => $imageObjects, // Include full image objects with metadata
                    'stockQuantity' => (int)$product->stock_quantity,
                    'specifications' => $product->specifications,
                    'status' => $product->status,
                    'rating' => $product->rating ? (float)$product->rating : null,
                    'totalReviews' => (int)$product->total_reviews,
                    'isFeatured' => (bool)$product->is_featured,
                    'viewCount' => (int)$product->view_count,
                    'createdAt' => $product->created_at->toISOString(),
                    'updatedAt' => $product->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $formattedProducts
                ],
                'meta' => [
                    'pagination' => [
                        'total' => $total,
                        'count' => $formattedProducts->count(),
                        'per_page' => (int)$limit,
                        'current_page' => (int)$page,
                        'total_pages' => (int)ceil($total / $limit)
                    ]
                ],
                'message' => 'Products retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a product
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $product = Product::where('id', $id)
                ->where('seller_id', $user->id)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or you don\'t have permission to edit it'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'category' => 'sometimes|string|max:100',
                'price' => 'sometimes|numeric|min:0',
                'stockQuantity' => 'sometimes|integer|min:0',
                'specifications' => 'nullable|array',
                'images' => 'sometimes|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'status' => 'sometimes|in:active,inactive,out_of_stock,discontinued'
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
                // Update fields
                $product->name = $request->name ?? $product->name;
                $product->description = $request->description ?? $product->description;
                $product->category = $request->category ?? $product->category;
                $product->price = $request->price ?? $product->price;
                $product->stock_quantity = $request->stockQuantity ?? $product->stock_quantity;
                $product->specifications = $request->specifications ?? $product->specifications;
                $product->status = $request->status ?? $product->status;
                
                $product->save();

                // Handle image uploads
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $imageFile) {
                        $path = $imageFile->store('product-images', 'public');
                        
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
                        
                        // Associate image with product
                        $product->images()->attach($image->id);
                    }
                }

                DB::commit();

                // Load relationships
                $product->load(['seller', 'images']);

                // Get image URLs
                $imageUrls = $product->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();

                // Get full image objects with metadata
                $imageObjects = $product->images->map(function ($image) {
                    return [
                        'id' => (string)$image->id,
                        'name' => $image->name,
                        'originalFilename' => $image->original_filename,
                        'url' => Storage::url($image->file_path),
                        'mimeType' => $image->mime_type,
                        'fileSize' => (int)$image->file_size,
                        'width' => $image->width,
                        'height' => $image->height,
                        'formattedSize' => $image->formatted_size,
                        'createdAt' => $image->created_at->toISOString(),
                        'updatedAt' => $image->updated_at->toISOString()
                    ];
                })->toArray();

                // Format product data
                $formattedProduct = [
                    'id' => (string)$product->id,
                    'sellerId' => (string)$product->seller_id,
                    'seller' => [
                        'id' => (string)$product->seller->id,
                        'username' => $product->seller->username,
                        'name' => $product->seller->name,
                        'country' => $product->seller->country,
                    ],
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (int)$product->price,
                    'currency' => 'NGN',
                    'category' => $product->category,
                    'images' => $imageObjects, // Include full image objects with metadata
                    'stockQuantity' => (int)$product->stock_quantity,
                    'specifications' => $product->specifications,
                    'status' => $product->status,
                    'rating' => $product->rating ? (float)$product->rating : null,
                    'totalReviews' => (int)$product->total_reviews,
                    'isFeatured' => (bool)$product->is_featured,
                    'viewCount' => (int)$product->view_count,
                    'createdAt' => $product->created_at->toISOString(),
                    'updatedAt' => $product->updated_at->toISOString()
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'data' => ['product' => $formattedProduct]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a product
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();

            $product = Product::where('id', $id)
                ->where('seller_id', $user->id)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or you don\'t have permission to delete it'
                ], 404);
            }

            $product->update(['status' => 'discontinued']);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}