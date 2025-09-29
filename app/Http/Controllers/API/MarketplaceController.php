<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class MarketplaceController extends Controller
{
    /**
     * Get all products with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['seller'])
                ->where('status', 'active')
                ->where('stock_quantity', '>', 0);

            // Apply filters
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            if ($request->has('minPrice') && $request->minPrice) {
                $query->where('price', '>=', $request->minPrice);
            }

            if ($request->has('maxPrice') && $request->maxPrice) {
                $query->where('price', '<=', $request->maxPrice);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('tags', 'LIKE', "%{$search}%");
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

            // Format products data to match documentation
            $formattedProducts = $products->map(function ($product) {
                return [
                    'id' => (string)$product->id,
                    'sellerId' => (string)$product->seller_id,
                    'title' => $product->name,
                    'description' => $product->description,
                    'price' => (int)$product->price,
                    'currency' => 'NGN',
                    'category' => $product->category,
                    'images' => $product->images,
                    'status' => $product->status,
                    'stock' => (int)$product->stock_quantity,
                    'location' => $product->location,
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
     * Get available product categories
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
            $product = Product::with(['seller'])
                ->where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Increment view count
            $product->increment('view_count');

            // Format product data to match documentation
            $formattedProduct = [
                'id' => (string)$product->id,
                'sellerId' => (string)$product->seller_id,
                'title' => $product->name,
                'description' => $product->description,
                'price' => (int)$product->price,
                'currency' => 'NGN',
                'category' => $product->category,
                'images' => $product->images,
                'status' => $product->status,
                'stock' => (int)$product->stock_quantity,
                'location' => $product->location,
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
     * Purchase a product
     */
    public function purchase(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $quantity = $request->quantity;

            // Find the product
            $product = Product::where('id', $id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Check if user is trying to buy their own product
            if ($product->seller_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot purchase your own product'
                ], 400);
            }

            // Check stock availability
            if ($product->stock_quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock available'
                ], 400);
            }

            $totalPrice = $product->price * $quantity;

            // Check user wallet balance
            if ($user->wallet_balance < $totalPrice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient wallet balance'
                ], 400);
            }

            // Process the purchase in a transaction
            DB::beginTransaction();

            try {
                // Deduct from buyer's wallet
                $user->deductFromWallet($totalPrice);

                // Add to seller's wallet (with platform commission)
                $platformCommission = $totalPrice * 0.05; // 5% platform fee
                $sellerAmount = $totalPrice - $platformCommission;
                
                $seller = User::find($product->seller_id);
                $seller->addToWallet($sellerAmount);

                // Update product stock
                $product->decrement('stock_quantity', $quantity);
                $product->increment('sales_count', $quantity);

                // Create transaction records
                $purchaseTransaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'purchase',
                    'amount' => $totalPrice,
                    'description' => "Purchased {$quantity}x {$product->name}",
                    'status' => 'completed',
                    'reference_type' => 'App\Models\Product',
                    'reference_id' => $product->id,
                    'metadata' => [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'seller_id' => $product->seller_id
                    ]
                ]);

                $salesTransaction = Transaction::create([
                    'user_id' => $seller->id,
                    'type' => 'earning',
                    'amount' => $sellerAmount,
                    'description' => "Sale of {$quantity}x {$product->name}",
                    'status' => 'completed',
                    'reference_type' => 'App\Models\Product',
                    'reference_id' => $product->id,
                    'metadata' => [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'buyer_id' => $user->id,
                        'platform_commission' => $platformCommission
                    ]
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Product purchased successfully',
                    'data' => [
                        'transaction_id' => (string)$purchaseTransaction->id,
                        'total_paid' => (int)$totalPrice,
                        'quantity' => (int)$quantity,
                        'remaining_balance' => (int)$user->fresh()->wallet_balance
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase failed',
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
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0.01',
                'stock' => 'required|integer|min:1',
                'category' => 'required|string|max:100',
                'images' => 'required|array|min:1|max:5',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'location' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Handle image uploads
            $imageUrls = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product-images', 'public');
                    $imageUrls[] = Storage::url($path);
                }
            }

            // Create the product
            $product = Product::create([
                'seller_id' => $user->id,
                'name' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'stock_quantity' => $request->stock,
                'category' => $request->category,
                'images' => $imageUrls,
                'location' => $request->location,
                'status' => 'pending_review'
            ]);

            // Format product data to match documentation
            $formattedProduct = [
                'id' => (string)$product->id,
                'sellerId' => (string)$product->seller_id,
                'title' => $product->name,
                'description' => $product->description,
                'price' => (int)$product->price,
                'currency' => 'NGN',
                'category' => $product->category,
                'images' => $product->images,
                'status' => $product->status,
                'stock' => (int)$product->stock_quantity,
                'location' => $product->location,
                'createdAt' => $product->created_at->toISOString(),
                'updatedAt' => $product->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => ['product' => $formattedProduct]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's own products
     */
    public function myProducts(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Product::where('seller_id', $user->id)
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

            // Format products data to match documentation
            $formattedProducts = $products->map(function ($product) {
                return [
                    'id' => (string)$product->id,
                    'sellerId' => (string)$product->seller_id,
                    'title' => $product->name,
                    'description' => $product->description,
                    'price' => (int)$product->price,
                    'currency' => 'NGN',
                    'category' => $product->category,
                    'images' => $product->images,
                    'status' => $product->status,
                    'stock' => (int)$product->stock_quantity,
                    'location' => $product->location,
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
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0.01',
                'stock' => 'sometimes|integer|min:0',
                'category' => 'sometimes|string|max:100',
                'images' => 'sometimes|array|max:5',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'location' => 'sometimes|string|max:255',
                'status' => 'sometimes|in:active,inactive,pending_review'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle new image uploads if provided
            if ($request->hasFile('images')) {
                $imageUrls = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product-images', 'public');
                    $imageUrls[] = Storage::url($path);
                }
                $product->images = $imageUrls;
            }

            // Update other fields
            $product->name = $request->title ?? $product->name;
            $product->description = $request->description ?? $product->description;
            $product->price = $request->price ?? $product->price;
            $product->stock_quantity = $request->stock ?? $product->stock_quantity;
            $product->category = $request->category ?? $product->category;
            $product->location = $request->location ?? $product->location;
            $product->status = $request->status ?? $product->status;
            
            $product->save();

            // Format product data to match documentation
            $formattedProduct = [
                'id' => (string)$product->id,
                'sellerId' => (string)$product->seller_id,
                'title' => $product->name,
                'description' => $product->description,
                'price' => (int)$product->price,
                'currency' => 'NGN',
                'category' => $product->category,
                'images' => $product->images,
                'status' => $product->status,
                'stock' => (int)$product->stock_quantity,
                'location' => $product->location,
                'createdAt' => $product->created_at->toISOString(),
                'updatedAt' => $product->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => ['product' => $formattedProduct]
            ]);

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

            $product->update(['status' => 'deleted']);

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

    /**
     * Get purchase history for the authenticated user
     */
    public function purchaseHistory(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Transaction::with(['referenceable'])
                ->where('user_id', $user->id)
                ->where('type', 'purchase')
                ->where('reference_type', 'App\Models\Product')
                ->orderBy('created_at', 'desc');

            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $total = $query->count();
            $transactions = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format transactions data to match documentation
            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'id' => (string)$transaction->id,
                    'productId' => (string)$transaction->reference_id,
                    'title' => $transaction->referenceable ? $transaction->referenceable->name : 'Product',
                    'quantity' => isset($transaction->metadata['quantity']) ? (int)$transaction->metadata['quantity'] : 1,
                    'price' => (int)$transaction->amount,
                    'currency' => 'NGN',
                    'status' => $transaction->status,
                    'purchasedAt' => $transaction->created_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'purchases' => $formattedTransactions
                ],
                'meta' => [
                    'pagination' => [
                        'total' => $total,
                        'count' => $formattedTransactions->count(),
                        'per_page' => (int)$limit,
                        'current_page' => (int)$page,
                        'total_pages' => (int)ceil($total / $limit)
                    ]
                ],
                'message' => 'Purchase history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch purchase history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales history for the authenticated user
     */
    public function salesHistory(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Transaction::with(['referenceable'])
                ->where('user_id', $user->id)
                ->where('type', 'earning')
                ->where('reference_type', 'App\Models\Product')
                ->orderBy('created_at', 'desc');

            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $total = $query->count();
            $transactions = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format transactions data to match documentation
            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'id' => (string)$transaction->id,
                    'productId' => (string)$transaction->reference_id,
                    'title' => $transaction->referenceable ? $transaction->referenceable->name : 'Product',
                    'quantity' => isset($transaction->metadata['quantity']) ? (int)$transaction->metadata['quantity'] : 1,
                    'price' => (int)$transaction->amount,
                    'currency' => 'NGN',
                    'status' => $transaction->status,
                    'soldAt' => $transaction->created_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'sales' => $formattedTransactions
                ],
                'meta' => [
                    'pagination' => [
                        'total' => $total,
                        'count' => $formattedTransactions->count(),
                        'per_page' => (int)$limit,
                        'current_page' => (int)$page,
                        'total_pages' => (int)ceil($total / $limit)
                    ]
                ],
                'message' => 'Sales history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}