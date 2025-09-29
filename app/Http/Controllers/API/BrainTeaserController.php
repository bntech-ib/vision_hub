<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BrainTeaser;
use App\Models\BrainTeaserAttempt;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BrainTeaserController extends Controller
{
    /**
     * Get available brain teasers for user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $limit = $request->get('limit', 10);
            
            // Check if user has brain teaser access based on their package
            if (!$user->hasBrainTeaserAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your current package does not include access to brain teasers.'
                ], 403);
            }
            
            // Get available brain teasers for the user
            $query = $user->getAvailableBrainTeasersQuery();
                
            $brainTeasers = $query->latest()
                ->limit($limit)
                ->get();
                
            // Format brain teasers to match documentation
            $formattedBrainTeasers = $brainTeasers->map(function ($brainTeaser) use ($user) {
                // Check if user has already attempted this brain teaser
                $userAttempt = BrainTeaserAttempt::where('user_id', $user->id)
                    ->where('brain_teaser_id', $brainTeaser->id)
                    ->first();

                // Format brain teaser data to match documentation
                $formattedBrainTeaser = [
                    'id' => (string)$brainTeaser->id,
                    'title' => $brainTeaser->title,
                    'question' => $brainTeaser->question,
                    'options' => $brainTeaser->options,
                    'category' => $brainTeaser->category,
                    'difficulty' => $brainTeaser->difficulty,
                    'rewardAmount' => (int)$brainTeaser->reward_amount,
                    'startDate' => $brainTeaser->start_date ? $brainTeaser->start_date->toISOString() : null,
                    'endDate' => $brainTeaser->end_date ? $brainTeaser->end_date->toISOString() : null,
                    'status' => $brainTeaser->status,
                    'createdAt' => $brainTeaser->created_at->toISOString()
                ];

                // Add user attempt information if exists
                if ($userAttempt) {
                    $formattedBrainTeaser['userAttempt'] = [
                        'attempted' => true,
                        'isCorrect' => (bool)$userAttempt->is_correct,
                        'attemptedAt' => $userAttempt->attempted_at ? $userAttempt->attempted_at->toISOString() : null,
                        'rewardEarned' => (int)$userAttempt->reward_earned
                    ];
                } else {
                    $formattedBrainTeaser['userAttempt'] = [
                        'attempted' => false
                    ];
                }

                return $formattedBrainTeaser;
            });

            return response()->json([
                'success' => true,
                'data' => ['brainTeasers' => $formattedBrainTeasers],
                'message' => 'Brain teasers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brain teasers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific brain teaser
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user has brain teaser access based on their package
            if (!$user->hasBrainTeaserAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your current package does not include access to brain teasers.'
                ], 403);
            }
            
            $brainTeaser = BrainTeaser::where('status', 'active')
                ->where('id', $id)
                ->first();
                
            if (!$brainTeaser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brain teaser not found or not active'
                ], 404);
            }

            // Check if user has already attempted this brain teaser
            $userAttempt = BrainTeaserAttempt::where('user_id', $user->id)
                ->where('brain_teaser_id', $brainTeaser->id)
                ->first();

            // Format brain teaser data to match documentation
            $formattedBrainTeaser = [
                'id' => (string)$brainTeaser->id,
                'title' => $brainTeaser->title,
                'question' => $brainTeaser->question,
                'options' => $brainTeaser->options,
                'category' => $brainTeaser->category,
                'difficulty' => $brainTeaser->difficulty,
                'rewardAmount' => (int)$brainTeaser->reward_amount,
                'startDate' => $brainTeaser->start_date ? $brainTeaser->start_date->toISOString() : null,
                'endDate' => $brainTeaser->end_date ? $brainTeaser->end_date->toISOString() : null,
                'status' => $brainTeaser->status,
                'createdAt' => $brainTeaser->created_at->toISOString()
            ];

            // Add correct answer and explanation if user has attempted or is admin
            if ($userAttempt || ($user && $user->isAdmin())) {
                $formattedBrainTeaser['correctAnswer'] = $brainTeaser->correct_answer;
                $formattedBrainTeaser['explanation'] = $brainTeaser->explanation;
            }

            // Add user attempt information if exists
            if ($userAttempt) {
                $formattedBrainTeaser['userAttempt'] = [
                    'attempted' => true,
                    'isCorrect' => (bool)$userAttempt->is_correct,
                    'attemptedAt' => $userAttempt->attempted_at ? $userAttempt->attempted_at->toISOString() : null,
                    'rewardEarned' => (int)$userAttempt->reward_earned
                ];
            } else {
                $formattedBrainTeaser['userAttempt'] = [
                    'attempted' => false
                ];
            }

            return response()->json([
                'success' => true,
                'data' => ['brainTeaser' => $formattedBrainTeaser],
                'message' => 'Brain teaser retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brain teaser',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get daily brain teaser
     */
    public function daily(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user has brain teaser access based on their package
            if (!$user->hasBrainTeaserAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your current package does not include access to brain teasers.'
                ], 403);
            }
            
            // Get today's date
            $today = Carbon::today();
            
            // Find active daily brain teaser for today
            $dailyBrainTeaser = BrainTeaser::where('status', 'active')
                ->where('is_daily', true)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
                
            if (!$dailyBrainTeaser) {
                return response()->json([
                    'success' => false,
                    'message' => 'No daily brain teaser available today'
                ], 404);
            }

            // Check if user has already attempted this brain teaser
            $userAttempt = null;
            if ($user) {
                $userAttempt = BrainTeaserAttempt::where('user_id', $user->id)
                    ->where('brain_teaser_id', $dailyBrainTeaser->id)
                    ->first();
            }

            // Format brain teaser data to match documentation
            $formattedBrainTeaser = [
                'id' => (string)$dailyBrainTeaser->id,
                'title' => $dailyBrainTeaser->title,
                'question' => $dailyBrainTeaser->question,
                'options' => $dailyBrainTeaser->options,
                'category' => $dailyBrainTeaser->category,
                'difficulty' => $dailyBrainTeaser->difficulty,
                'rewardAmount' => (int)$dailyBrainTeaser->reward_amount,
                'startDate' => $dailyBrainTeaser->start_date ? $dailyBrainTeaser->start_date->toISOString() : null,
                'endDate' => $dailyBrainTeaser->end_date ? $dailyBrainTeaser->end_date->toISOString() : null,
                'status' => $dailyBrainTeaser->status,
                'createdAt' => $dailyBrainTeaser->created_at->toISOString()
            ];

            // Add correct answer and explanation if user has attempted or is admin
            if ($userAttempt || ($user && $user->isAdmin())) {
                $formattedBrainTeaser['correctAnswer'] = $dailyBrainTeaser->correct_answer;
                $formattedBrainTeaser['explanation'] = $dailyBrainTeaser->explanation;
            }

            // Add user attempt information if exists
            if ($userAttempt) {
                $formattedBrainTeaser['userAttempt'] = [
                    'attempted' => true,
                    'isCorrect' => (bool)$userAttempt->is_correct,
                    'attemptedAt' => $userAttempt->attempted_at ? $userAttempt->attempted_at->toISOString() : null,
                    'rewardEarned' => (int)$userAttempt->reward_earned
                ];
            } else {
                $formattedBrainTeaser['userAttempt'] = [
                    'attempted' => false
                ];
            }

            return response()->json([
                'success' => true,
                'data' => ['brainTeaser' => $formattedBrainTeaser],
                'message' => 'Daily brain teaser retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve daily brain teaser',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit answer for a brain teaser
     */
    public function submitAnswer(Request $request, string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user has brain teaser access based on their package
            if (!$user->hasBrainTeaserAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your current package does not include access to brain teasers.'
                ], 403);
            }
            
            // Validate request
            // Convert answer to string if it's not already
            $requestData = $request->all();
            if (isset($requestData['answer']) && !is_string($requestData['answer'])) {
                $requestData['answer'] = (string)$requestData['answer'];
            }
            
            $validator = Validator::make($requestData, [
                'answer' => 'required|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Update the request with the converted data
            $request->merge($requestData);
            
            // Find the brain teaser
            $brainTeaser = BrainTeaser::where('status', 'active')
                ->where('id', $id)
                ->first();
                
            if (!$brainTeaser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brain teaser not found or not active'
                ], 404);
            }
            
            // Check if user has already attempted this brain teaser
            $existingAttempt = BrainTeaserAttempt::where('user_id', $user->id)
                ->where('brain_teaser_id', $brainTeaser->id)
                ->first();
                
            if ($existingAttempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already attempted this brain teaser'
                ], 400);
            }
            
            // Check if the brain teaser is active and within date range
            if (!$brainTeaser->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This brain teaser is not currently active'
                ], 400);
            }
            
            // Check the answer
            $isCorrect = $brainTeaser->isCorrectAnswer($request->answer);
            
            // Create attempt record
            $attempt = BrainTeaserAttempt::create([
                'user_id' => $user->id,
                'brain_teaser_id' => $brainTeaser->id,
                'answer' => $request->answer,
                'is_correct' => $isCorrect,
                'attempted_at' => now(),
                'reward_earned' => $isCorrect ? $brainTeaser->reward_amount : 0
            ]);
            
            // Update brain teaser statistics
            $brainTeaser->increment('total_attempts');
            if ($isCorrect) {
                $brainTeaser->increment('correct_attempts');
            }
            
            // Award reward if correct
            $rewardEarned = 0;
            if ($isCorrect) {
                $rewardEarned = $brainTeaser->reward_amount;
                
                // Create transaction for reward
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'earning',
                    'amount' => $rewardEarned,
                    'description' => 'Brain teaser reward',
                    'status' => 'completed',
                    'reference_type' => BrainTeaser::class,
                    'reference_id' => $brainTeaser->id
                ]);
                
                // Update user wallet balance
                $user->increment('wallet_balance', $rewardEarned);
            }
            
            // Format response
            $response = [
                'id' => (string)$attempt->id,
                'brainTeaserId' => (string)$brainTeaser->id,
                'answer' => $request->answer,
                'isCorrect' => (bool)$isCorrect,
                'attemptedAt' => $attempt->attempted_at->toISOString(),
                'rewardEarned' => (int)$rewardEarned,
                'correctAnswer' => $brainTeaser->correct_answer,
                'explanation' => $brainTeaser->explanation
            ];
            
            return response()->json([
                'success' => true,
                'data' => ['attempt' => $response],
                'message' => $isCorrect ? 'Correct answer! Reward earned.' : 'Incorrect answer. Better luck next time!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit answer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's brain teaser history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $limit = $request->get('limit', 10);
            
            // Get user's brain teaser attempts with related brain teaser data
            $attempts = BrainTeaserAttempt::where('user_id', $user->id)
                ->with('brainTeaser')
                ->latest()
                ->limit($limit)
                ->get();
                
            // Format attempts
            $formattedAttempts = $attempts->map(function ($attempt) {
                return [
                    'id' => (string)$attempt->id,
                    'brainTeaser' => [
                        'id' => (string)$attempt->brainTeaser->id,
                        'title' => $attempt->brainTeaser->title,
                        'category' => $attempt->brainTeaser->category,
                        'difficulty' => $attempt->brainTeaser->difficulty
                    ],
                    'answer' => $attempt->answer,
                    'isCorrect' => (bool)$attempt->is_correct,
                    'attemptedAt' => $attempt->attempted_at->toISOString(),
                    'rewardEarned' => (int)$attempt->reward_earned
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => ['attempts' => $formattedAttempts],
                'message' => 'Brain teaser history retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brain teaser history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get brain teaser statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get user's brain teaser statistics
            $totalAttempts = BrainTeaserAttempt::where('user_id', $user->id)->count();
            $correctAttempts = BrainTeaserAttempt::where('user_id', $user->id)->where('is_correct', true)->count();
            
            // Calculate accuracy percentage
            $accuracy = $totalAttempts > 0 ? round(($correctAttempts / $totalAttempts) * 100, 2) : 0;
            
            // Get total available brain teasers
            $totalBrainTeasers = BrainTeaser::where('status', 'active')->count();
            
            // Get user's completed brain teasers
            $completedBrainTeasers = BrainTeaserAttempt::where('user_id', $user->id)
                ->distinct('brain_teaser_id')
                ->count('brain_teaser_id');
                
            // Get rewards earned
            $totalRewards = BrainTeaserAttempt::where('user_id', $user->id)
                ->where('is_correct', true)
                ->sum('reward_earned');
            
            $stats = [
                'totalAttempts' => $totalAttempts,
                'correctAttempts' => $correctAttempts,
                'accuracy' => $accuracy,
                'totalAvailable' => $totalBrainTeasers,
                'completed' => $completedBrainTeasers,
                'completionRate' => $totalBrainTeasers > 0 ? round(($completedBrainTeasers / $totalBrainTeasers) * 100, 2) : 0,
                'totalRewardsEarned' => (int)$totalRewards
            ];
            
            return response()->json([
                'success' => true,
                'data' => ['stats' => $stats],
                'message' => 'Brain teaser statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brain teaser statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}