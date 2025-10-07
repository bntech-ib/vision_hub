<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Advertisement;
use App\Models\Product;
use App\Models\Course;
use App\Models\BrainTeaser;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get user dashboard statistics
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        // Load user with current package
        $user->load('currentPackage');
        
        // Get user statistics
        $stats = [
            'totalEarnings' => (int)$user->transactions()
                ->where('type', 'earning')
                ->where('status', 'completed')
                ->sum('amount'),
            'availableBalance' => (int)$user->wallet_balance,
            'referralEarnings' => (int)$user->referral_earnings,
            'pendingEarnings' => (int)$user->transactions()
                ->where('type', 'earning')
                ->where('status', 'pending')
                ->sum('amount'),
            'totalWithdrawals' => (int)$user->transactions()
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount'),
            'referralCount' => $user->referrals()->count(),
            'activeAds' => Advertisement::where('status', 'active')
                ->where('start_date', '<=', now())
                ->where(function ($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->count(),
            'currency' => 'NGN'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats
            ],
            'message' => 'Dashboard stats retrieved successfully'
        ]);
    }

    /**
     * Get user earnings history
     */
    public function earnings(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $limit = $request->get('limit', 10);
        $page = $request->get('page', 1);

        // Get earnings transactions
        $earningsQuery = $user->transactions()
            ->whereIn('type', ['earning', 'referral_earning'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc');

        $total = $earningsQuery->count();
        $earnings = $earningsQuery->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        // Transform earnings data
        $earningsData = $earnings->map(function ($transaction) {
            return [
                'id' => (string)$transaction->id,
                'amount' => (int)$transaction->amount,
                'type' => $transaction->type, // 'earning' or 'referral_earning'
                'source' => $this->getTransactionSource($transaction),
                'description' => $transaction->description,
                'date' => $transaction->created_at->toISOString()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'earnings' => $earningsData,
                'summary' => [
                    'normalEarnings' => (int)$user->getNormalEarnings(),
                    'referralEarnings' => (int)$user->getReferralEarnings(),
                    'totalEarnings' => (int)$user->getTotalEarnings(),
                    'todayEarnings' => (int)$user->getTodayEarnings()
                ]
            ],
            'meta' => [
                'pagination' => [
                    'total' => $total,
                    'count' => $earningsData->count(),
                    'per_page' => (int)$limit,
                    'current_page' => (int)$page,
                    'total_pages' => (int)ceil($total / $limit)
                ]
            ],
            'message' => 'Earnings history retrieved successfully'
        ]);
    }

    /**
     * Get user notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 10);
        $page = $request->get('page', 1);

        // For now, we'll create some sample notifications
        // In a real implementation, this would come from a notifications table
        $notifications = [
            [
                'id' => '1',
                'userId' => (string)$user->id,
                'title' => 'Payment Received',
                'message' => 'You\'ve received ₦250.00 from ad view',
                'type' => 'earning',
                'read' => false,
                'createdAt' => now()->toISOString()
            ]
        ];

        $total = count($notifications);
        $paginatedNotifications = array_slice($notifications, ($page - 1) * $limit, $limit);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $paginatedNotifications
            ],
            'meta' => [
                'pagination' => [
                    'total' => $total,
                    'count' => count($paginatedNotifications),
                    'per_page' => (int)$limit,
                    'current_page' => (int)$page,
                    'total_pages' => (int)ceil($total / $limit)
                ]
            ],
            'message' => 'Notifications retrieved successfully'
        ]);
    }

    /**
     * Get detailed referral statistics
     */
    public function referralStats(): JsonResponse
    {
        $user = Auth::user();
        
        $referralStats = $user->getDetailedReferralStats();
        
        return response()->json([
            'success' => true,
            'data' => $referralStats,
            'message' => 'Referral statistics retrieved successfully'
        ]);
    }

    /**
     * Get system statistics (for admin users)
     */
    public function systemStats(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::whereHas('adInteractions', function($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            })->count(),
            'total_ads' => Advertisement::count(),
            'active_ads' => Advertisement::where('status', 'active')->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'total_courses' => Course::count(),
            'published_courses' => Course::where('status', 'published')->count(),
            'total_brain_teasers' => BrainTeaser::count(),
            'active_brain_teasers' => BrainTeaser::where('is_active', true)->count(),
            'total_transactions' => Transaction::count(),
            'completed_transactions' => Transaction::where('status', 'completed')->count(),
            'total_transaction_volume' => Transaction::where('status', 'completed')->sum('amount'),
        ];

        // Recent activity
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get(['id', 'name', 'email', 'created_at']);
        $recentTransactions = Transaction::with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'recent_users' => $recentUsers,
                'recent_transactions' => $recentTransactions,
            ]
        ]);
    }

    /**
     * Get available ads count for user
     */
    public function availableAds(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->hasActivePackage()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'available_ads' => 0,
                    'message' => 'Active package required to view ads'
                ]
            ]);
        }

        // Check if user has already interacted with an ad today
        $alreadyInteracted = $user->adInteractions()
            ->whereDate('interacted_at', today())
            ->exists();
            
        $availableAds = 0;
        if (!$alreadyInteracted) {
            $availableAds = Advertisement::where('status', 'active')
                ->where('start_date', '<=', now())
                ->where(function ($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->where(function ($query) {
                    $query->where('max_views', 0)
                          ->orWhereRaw('current_views < max_views');
                })
                // Also check that ad spend hasn't reached budget
                ->where(function ($query) {
                    $query->where('budget', 0)
                          ->orWhereRaw('spent < budget');
                })
                // Exclude ads that the user has already interacted with today
                ->whereNotIn('id', function($query) use ($user) {
                    $query->select('advertisement_id')
                          ->from('ad_interactions')
                          ->where('user_id', $user->id)
                          ->whereDate('interacted_at', today());
                })
                ->limit(1) // Only one ad per day
                ->count();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'available_ads' => $availableAds,
                'package_limit' => $user->currentPackage->ad_views_limit ?? null,
                'viewed_today' => $user->adInteractions()
                    ->where('type', 'view')
                    ->whereDate('interacted_at', today())
                    ->count(),
            ]
        ]);
    }

    /**
     * Get transaction source label
     */
    private function getTransactionSource($transaction)
    {
        switch ($transaction->reference_type) {
            case 'App\Models\Advertisement':
                return 'ad_view';
            case 'App\Models\BrainTeaser':
                return 'brain_teaser';
            case 'App\Models\Product':
                return 'product_sale';
            case 'App\Models\Course':
                return 'course_sale';
            default:
                return 'other';
        }
    }
}