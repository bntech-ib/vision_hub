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
            /** @var User $user */
            $user = Auth::user();
            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);
            
            // Check if user has reached their daily ad interaction limit (only 1 ad per day now)
            if ($user->hasReachedDailyAdInteractionLimit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached your daily ad interaction limit. You can only interact with one ad per day.'
                ], 403);
            }
            
            // Get available ads for the user (limited to 1 ad per day)
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
            
            // Limit to only 1 ad per day
            $limit = 1;
            
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
            /** @var User $user */
            $user = Auth::user();
            // Check if user is admin
            if (!$user->is_admin) {
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
            /** @var User $user */
            $user = Auth::user();
            // Check if user is admin
            if (!$user->is_admin) {
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
            /** @var User $user */
            $user = Auth::user();
            // Check if user is admin
            if (!$user->is_admin) {
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
            /** @var User $user */
            $user = Auth::user();
            
            // Log security event for ad interaction
            $user->logSecurityEvent('ad_interaction_attempt', [
                'ad_id' => $id,
                'type' => $request->type ?? 'unknown',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            // Validate request
            $validated = $request->validate([
                'type' => 'required|in:view,click'
            ]);
            
            // Check if user has reached their daily ad interaction limit (for both views and clicks)
            if ($user->hasReachedDailyAdInteractionLimit()) {
                $user->logSecurityEvent('ad_interaction_blocked', [
                    'reason' => 'daily_limit_reached',
                    'ad_id' => $id,
                    'type' => $validated['type']
                ], false); // false = unsuccessful attempt
                
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached your daily ad interaction limit based on your package.'
                ], 403);
            }
            
            // Check if ad is active
            if (!$ad->isActive()) {
                $user->logSecurityEvent('ad_interaction_blocked', [
                    'reason' => 'ad_not_active',
                    'ad_id' => $id,
                    'type' => $validated['type']
                ], false);
                
                return response()->json([
                    'success' => false,
                    'message' => 'This advertisement is not currently active'
                ], 400);
            }
            
            // Check if user has already interacted with this ad today
            $alreadyInteracted = AdInteraction::where('user_id', $user->id)
                ->where('advertisement_id', $ad->id)
                ->whereDate('interacted_at', today())
                ->exists();
                
            if ($alreadyInteracted) {
                $user->logSecurityEvent('ad_interaction_blocked', [
                    'reason' => 'already_interacted_today',
                    'ad_id' => $id,
                    'type' => $validated['type']
                ], false);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You have already interacted with this advertisement today. Please try again tomorrow.'
                ], 403);
            }
            
            // Calculate earning based on user's package
            $earningPerAd = 0;
            if ($user->hasActivePackage()) {
                $earningPerAd = $user->currentPackage->calculateEarningPerAd();
            }
            
            // Record interaction based on type
            if ($validated['type'] === 'view') {
                // Record view
                try {
                    DB::beginTransaction();

                    // For the new system, award all daily earnings from one interaction
                    $totalDailyEarnings = 0;
                    if ($user->hasActivePackage() && $user->currentPackage->daily_earning_limit > 0) {
                        $totalDailyEarnings = $user->currentPackage->daily_earning_limit;
                    }

                    // Record interaction
                    $interaction = AdInteraction::create([
                        'user_id' => $user->id,
                        'advertisement_id' => $ad->id,
                        'type' => 'view',
                        'reward_earned' => $totalDailyEarnings,
                        'interacted_at' => now()
                    ]);

                    // Update ad statistics
                    $ad->increment('impressions');
                    
                    // Update ad spend
                    if ($totalDailyEarnings > 0) {
                        $ad->increment('spent', $totalDailyEarnings);
                    }
                    
                    // Award all daily earnings to user
                    if ($totalDailyEarnings > 0) {
                        $user->addToWallet($totalDailyEarnings);
                        
                        // Create transaction record
                        Transaction::create([
                            'user_id' => $user->id,
                            'amount' => $totalDailyEarnings,
                            'type' => 'earning',
                            'description' => "Daily earnings from viewing advertisement #{$ad->id}",
                            'status' => 'completed'
                        ]);
                    }

                    DB::commit();
                    
                    // Log successful interaction
                    $user->logSecurityEvent('ad_interaction_success', [
                        'ad_id' => $id,
                        'type' => 'view',
                        'reward_earned' => $totalDailyEarnings
                    ], true); // true = successful attempt

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'interaction' => $interaction,
                            'total_earnings_awarded' => $totalDailyEarnings,
                            'remaining_interactions' => $user->getRemainingDailyAdInteractions()
                        ],
                        'message' => 'Ad view recorded successfully. You have received your daily earnings.'
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $user->logSecurityEvent('ad_interaction_error', [
                        'ad_id' => $id,
                        'type' => 'view',
                        'error' => $e->getMessage()
                    ], false);
                    throw $e;
                }
            } else {
                // Record click (same as view, since all earnings come from one interaction)
                try {
                    DB::beginTransaction();

                    // For the new system, award all daily earnings from one interaction
                    $totalDailyEarnings = 0;
                    if ($user->hasActivePackage() && $user->currentPackage->daily_earning_limit > 0) {
                        $totalDailyEarnings = $user->currentPackage->daily_earning_limit;
                    }

                    // Record interaction
                    $interaction = AdInteraction::create([
                        'user_id' => $user->id,
                        'advertisement_id' => $ad->id,
                        'type' => 'click',
                        'reward_earned' => $totalDailyEarnings,
                        'interacted_at' => now()
                    ]);

                    // Update ad statistics
                    $ad->increment('clicks');
                    
                    // Update ad spend
                    if ($totalDailyEarnings > 0) {
                        $ad->increment('spent', $totalDailyEarnings);
                    }
                    
                    // Award all daily earnings to user
                    if ($totalDailyEarnings > 0) {
                        $user->addToWallet($totalDailyEarnings);
                        
                        // Create transaction record
                        Transaction::create([
                            'user_id' => $user->id,
                            'amount' => $totalDailyEarnings,
                            'type' => 'earning',
                            'description' => "Daily earnings from clicking advertisement #{$ad->id}",
                            'status' => 'completed'
                        ]);
                    }

                    DB::commit();
                    
                    // Log successful interaction
                    $user->logSecurityEvent('ad_interaction_success', [
                        'ad_id' => $id,
                        'type' => 'click',
                        'reward_earned' => $totalDailyEarnings
                    ], true);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'interaction' => $interaction,
                            'total_earnings_awarded' => $totalDailyEarnings
                        ],
                        'message' => 'Ad click recorded successfully. You have received your daily earnings.'
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $user->logSecurityEvent('ad_interaction_error', [
                        'ad_id' => $id,
                        'type' => 'click',
                        'error' => $e->getMessage()
                    ], false);
                    throw $e;
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record ad interaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's ad statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            // Get today's interactions
            $todayInteractions = $user->adInteractions()
                ->whereDate('interacted_at', now()->toDateString())
                ->get();
                
            $todayViews = $todayInteractions->where('type', 'view')->count();
            $todayClicks = $todayInteractions->where('type', 'click')->count();
            
            // Get package limit
            $dailyLimit = 0;
            if ($user->hasActivePackage() && $user->currentPackage && $user->currentPackage->ad_limits) {
                $dailyLimit = $user->currentPackage->ad_limits;
            }
            
            // Calculate remaining interactions
            $remainingInteractions = $user->getRemainingDailyAdInteractions();

            return response()->json([
                'success' => true,
                'data' => [
                    'today_views' => $todayViews,
                    'today_clicks' => $todayClicks,
                    'daily_limit' => $dailyLimit,
                    'remaining_interactions' => $remainingInteractions,
                    'has_reached_limit' => $user->hasReachedDailyAdInteractionLimit()
                ],
                'message' => 'Ad statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ad statistics',
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
            /** @var User $user */
            $user = Auth::user();
            $limit = $request->get('limit', 15);
            $page = $request->get('page', 1);

            // Get user's ad interactions with advertisement details
            $interactions = AdInteraction::with('advertisement')
                ->where('user_id', $user->id)
                ->orderBy('interacted_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            // Format interactions data
            $formattedInteractions = $interactions->map(function ($interaction) {
                return [
                    'id' => (string)$interaction->id,
                    'advertisement_id' => (string)$interaction->advertisement_id,
                    'type' => $interaction->type,
                    'reward_earned' => (float)$interaction->reward_earned,
                    'interacted_at' => $interaction->interacted_at->toISOString(),
                    'advertisement' => $interaction->advertisement ? [
                        'id' => (string)$interaction->advertisement->id,
                        'title' => $interaction->advertisement->title,
                        'description' => $interaction->advertisement->description
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'interactions' => $formattedInteractions,
                    'meta' => [
                        'pagination' => [
                            'total' => $interactions->total(),
                            'count' => $interactions->count(),
                            'per_page' => $interactions->perPage(),
                            'current_page' => $interactions->currentPage(),
                            'total_pages' => $interactions->lastPage()
                        ]
                    ]
                ],
                'message' => 'Ad interactions retrieved successfully'
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