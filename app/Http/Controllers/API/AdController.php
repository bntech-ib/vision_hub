<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AdInteraction;
use App\Models\Advertisement;
use App\Models\Image;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdController extends Controller
{
    /**
     * Get all advertisements
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);
            
            // Check if user has reached their ad view limit
            if ($user->hasReachedAdViewLimit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached your ad view limit for this month based on your package.'
                ], 403);
            }
            
            // Get available ads for the user
            $query = $user->getAvailableAdsQuery();
                
            // Apply category filter if provided
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }
            
            // Apply search filter if provided
            if ($request->has('search') && $request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }
            
            // Apply pagination
            $ads = $query->paginate($limit, ['*'], 'page', $page);
            
            // Format ads to match documentation
            $formattedAds = $ads->map(function ($ad) {
                return [
                    'id' => (string)$ad->id,
                    'title' => $ad->title,
                    'description' => $ad->description,
                    'imageUrl' => $ad->image_url ? asset('storage/' . $ad->image_url) : null,
                    'targetUrl' => $ad->target_url,
                    'category' => $ad->category,
                    'rewardAmount' => (int)$ad->reward_amount,
                    'startDate' => $ad->start_date->toISOString(),
                    'endDate' => $ad->end_date ? $ad->end_date->toISOString() : null,
                    'status' => $ad->status,
                    'createdAt' => $ad->created_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'ads' => $formattedAds,
                    'meta' => [
                        'pagination' => [
                            'total' => $ads->total(),
                            'count' => $ads->count(),
                            'per_page' => $ads->perPage(),
                            'current_page' => $ads->currentPage(),
                            'total_pages' => $ads->lastPage()
                        ]
                    ]
                ],
                'message' => 'Advertisements retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve advertisements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific advertisement
     */
    public function show($id): JsonResponse
    {
        try {
            $ad = Advertisement::findOrFail($id);
            
            // Check if ad is active
            if (!$ad->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This advertisement is not currently active'
                ], 400);
            }
            
            // Format ad to match documentation
            $formattedAd = [
                'id' => (string)$ad->id,
                'title' => $ad->title,
                'description' => $ad->description,
                'imageUrl' => $ad->image_url ? asset('storage/' . $ad->image_url) : null,
                'targetUrl' => $ad->target_url,
                'category' => $ad->category,
                'rewardAmount' => (int)$ad->reward_amount,
                'startDate' => $ad->start_date->toISOString(),
                'endDate' => $ad->end_date ? $ad->end_date->toISOString() : null,
                'status' => $ad->status,
                'createdAt' => $ad->created_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'ad' => $formattedAd
                ],
                'message' => 'Advertisement retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve advertisement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new advertisement (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Check if user is admin
            if (!Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'target_url' => 'required|url',
                'category' => 'required|string',
                'budget' => 'required|numeric|min:0',
                'reward_amount' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'status' => 'required|in:pending,active,paused,completed,rejected',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            DB::beginTransaction();

            try {
                // Handle image upload
                $imageUrl = null;
                if ($request->hasFile('image')) {
                    $imageUrl = $request->file('image')->store('ad-images', 'public');
                }

                // Create advertisement
                $ad = Advertisement::create(array_merge($validated, [
                    'image_url' => $imageUrl,
                    'advertiser_id' => Auth::id(),
                    'spent' => 0,
                    'impressions' => 0,
                    'clicks' => 0
                ]));

                DB::commit();

                // Format response to match documentation
                $formattedAd = [
                    'id' => (string)$ad->id,
                    'title' => $ad->title,
                    'description' => $ad->description,
                    'imageUrl' => $ad->image_url ? asset('storage/' . $ad->image_url) : null,
                    'targetUrl' => $ad->target_url,
                    'category' => $ad->category,
                    'rewardAmount' => (int)$ad->reward_amount,
                    'startDate' => $ad->start_date->toISOString(),
                    'endDate' => $ad->end_date ? $ad->end_date->toISOString() : null,
                    'status' => $ad->status,
                    'createdAt' => $ad->created_at->toISOString()
                ];

                return response()->json([
                    'success' => true,
                    'data' => [
                        'ad' => $formattedAd
                    ],
                    'message' => 'Advertisement created successfully'
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create advertisement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an advertisement (admin only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Check if user is admin
            if (!Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $ad = Advertisement::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'target_url' => 'sometimes|url',
                'category' => 'sometimes|string',
                'budget' => 'sometimes|numeric|min:0',
                'reward_amount' => 'sometimes|numeric|min:0',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after:start_date',
                'status' => 'sometimes|in:pending,active,paused,completed,rejected',
                'targeting' => 'nullable|array',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'images' => 'sometimes|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            DB::beginTransaction();

            try {
                // Handle image upload
                if ($request->hasFile('image')) {
                    // Delete old image if exists
                    if ($ad->image_url) {
                        Storage::disk('public')->delete($ad->image_url);
                    }
                    $validated['image_url'] = $request->file('image')->store('ad-images', 'public');
                }

                $ad->update($validated);

                DB::commit();

                // Format response to match documentation
                $formattedAd = [
                    'id' => (string)$ad->id,
                    'title' => $ad->title,
                    'description' => $ad->description,
                    'imageUrl' => $ad->image_url ? asset('storage/' . $ad->image_url) : null,
                    'targetUrl' => $ad->target_url,
                    'category' => $ad->category,
                    'rewardAmount' => (int)$ad->reward_amount,
                    'startDate' => $ad->start_date->toISOString(),
                    'endDate' => $ad->end_date ? $ad->end_date->toISOString() : null,
                    'status' => $ad->status,
                    'createdAt' => $ad->created_at->toISOString()
                ];

                return response()->json([
                    'success' => true,
                    'data' => [
                        'ad' => $formattedAd
                    ],
                    'message' => 'Advertisement updated successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update advertisement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an advertisement (admin only)
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Check if user is admin
            if (!Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $ad = Advertisement::findOrFail($id);

            // Delete associated image
            if ($ad->image_url) {
                Storage::disk('public')->delete($ad->image_url);
            }

            $ad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Advertisement deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete advertisement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record ad interaction (view or click)
     */
    public function interact(Request $request, $id): JsonResponse
    {
        try {
            $ad = Advertisement::findOrFail($id);
            $user = Auth::user();
            
            // Validate request
            $validated = $request->validate([
                'type' => 'required|in:view,click'
            ]);
            
            // Check if user has reached their ad view limit (only for views)
            if ($validated['type'] === 'view' && $user->hasReachedAdViewLimit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached your ad view limit for this month based on your package.'
                ], 403);
            }
            
            // Check if ad is active
            if (!$ad->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This advertisement is not currently active'
                ], 400);
            }
            
            // Record interaction based on type
            if ($validated['type'] === 'view') {
                // Record view
                try {
                    DB::beginTransaction();

                    // Record interaction
                    $interaction = AdInteraction::create([
                        'user_id' => $user->id,
                        'advertisement_id' => $ad->id,
                        'type' => 'view',
                        'earnings' => $ad->reward_amount,
                        'interacted_at' => now()
                    ]);

                    // Update ad statistics
                    $ad->increment('impressions');
                    
                    // Award earnings to user
                    if ($ad->reward_amount > 0) {
                        $user->addToWallet($ad->reward_amount);
                        
                        // Create transaction record
                        Transaction::create([
                            'user_id' => $user->id,
                            'type' => 'earning',
                            'amount' => $ad->reward_amount,
                            'description' => "Advertisement view reward: {$ad->title}",
                            'status' => 'completed',
                            'reference_type' => 'App\Models\Advertisement',
                            'reference_id' => $ad->id,
                            'metadata' => [
                                'advertisement_id' => $ad->id,
                                'interaction_id' => $interaction->id
                            ]
                        ]);
                    }

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'interaction' => [
                                'id' => (string)$interaction->id,
                                'userId' => (string)$interaction->user_id,
                                'adId' => (string)$interaction->advertisement_id,
                                'type' => $interaction->type,
                                'earnings' => (int)$interaction->earnings,
                                'timestamp' => $interaction->interacted_at->toISOString()
                            ]
                        ],
                        'message' => 'Ad view recorded successfully'
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to record ad view',
                        'error' => $e->getMessage()
                    ], 500);
                }
            } else {
                // Record click
                try {
                    DB::beginTransaction();

                    // For clicks, we'll award a higher reward (e.g., 5x the view reward)
                    $clickReward = $ad->reward_amount * 5;

                    // Record interaction
                    $interaction = AdInteraction::create([
                        'user_id' => $user->id,
                        'advertisement_id' => $ad->id,
                        'type' => 'click',
                        'earnings' => $clickReward,
                        'interacted_at' => now()
                    ]);

                    // Update ad statistics
                    $ad->increment('clicks');
                    $ad->increment('spent', $clickReward);
                    
                    // Award earnings to user
                    if ($clickReward > 0) {
                        $user->addToWallet($clickReward);
                        
                        // Create transaction record
                        Transaction::create([
                            'user_id' => $user->id,
                            'type' => 'earning',
                            'amount' => $clickReward,
                            'description' => "Advertisement click reward: {$ad->title}",
                            'status' => 'completed',
                            'reference_type' => 'App\Models\Advertisement',
                            'reference_id' => $ad->id,
                            'metadata' => [
                                'advertisement_id' => $ad->id,
                                'interaction_id' => $interaction->id
                            ]
                        ]);
                    }

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'interaction' => [
                                'id' => (string)$interaction->id,
                                'userId' => (string)$interaction->user_id,
                                'adId' => (string)$interaction->advertisement_id,
                                'type' => $interaction->type,
                                'earnings' => (int)$interaction->earnings,
                                'timestamp' => $interaction->interacted_at->toISOString()
                            ]
                        ],
                        'message' => 'Ad click recorded successfully'
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to record ad click',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process ad interaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's ad interaction history
     */
    public function myInteractions(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $limit = $request->get('limit', 10);
            
            $interactions = AdInteraction::where('user_id', $user->id)
                ->with('advertisement:id,title')
                ->latest()
                ->limit($limit)
                ->get();
                
            // Format interactions to match documentation
            $formattedInteractions = $interactions->map(function ($interaction) {
                return [
                    'id' => (string)$interaction->id,
                    'userId' => (string)$interaction->user_id,
                    'adId' => (string)$interaction->advertisement_id,
                    'adTitle' => $interaction->advertisement->title ?? 'Unknown Ad',
                    'type' => $interaction->type,
                    'earnings' => (int)$interaction->earnings,
                    'timestamp' => $interaction->interacted_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'interactions' => $formattedInteractions
                ],
                'message' => 'Your ad interactions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ad interactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}