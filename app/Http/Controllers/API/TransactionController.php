<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Get user's transaction history
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('reference_type') && $request->reference_type) {
                $query->where('reference_type', $request->reference_type);
            }

            $limit = $request->get('limit', 15);
            $page = $request->get('page', 1);

            $total = $query->count();
            $transactions = $query->with(['referenceable'])
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format transactions data to match documentation
            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'id' => (string)$transaction->id,
                    'userId' => (string)$transaction->user_id,
                    'type' => $transaction->type,
                    'amount' => (int)$transaction->amount,
                    'currency' => 'NGN',
                    'description' => $transaction->description,
                    'status' => $transaction->status,
                    'referenceType' => $transaction->reference_type,
                    'referenceId' => $transaction->reference_id ? (string)$transaction->reference_id : null,
                    'createdAt' => $transaction->created_at->toISOString(),
                    'updatedAt' => $transaction->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $formattedTransactions
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
                'message' => 'Transactions retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single transaction details
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();

            $transaction = Transaction::with(['referenceable'])
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Format transaction data to match documentation
            $formattedTransaction = [
                'id' => (string)$transaction->id,
                'userId' => (string)$transaction->user_id,
                'type' => $transaction->type,
                'amount' => (int)$transaction->amount,
                'currency' => 'NGN',
                'description' => $transaction->description,
                'status' => $transaction->status,
                'referenceType' => $transaction->reference_type,
                'referenceId' => $transaction->reference_id ? (string)$transaction->reference_id : null,
                'metadata' => $transaction->metadata,
                'createdAt' => $transaction->created_at->toISOString(),
                'updatedAt' => $transaction->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => ['transaction' => $formattedTransaction],
                'message' => 'Transaction retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet summary and statistics
     */
    public function walletSummary(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Current wallet balance
            $currentBalance = $user->wallet_balance;

            // Total earnings (all time)
            $totalEarnings = Transaction::where('user_id', $user->id)
                ->where('type', 'earning')
                ->where('status', 'completed')
                ->sum('amount');

            // Total spending (all time)
            $totalSpending = Transaction::where('user_id', $user->id)
                ->where('type', 'purchase')
                ->where('status', 'completed')
                ->sum('amount');

            // Total withdrawals (all time)
            $totalWithdrawals = Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount');

            // This month statistics
            $thisMonthStart = Carbon::now()->startOfMonth();
            
            $thisMonthEarnings = Transaction::where('user_id', $user->id)
                ->where('type', 'earning')
                ->where('status', 'completed')
                ->where('created_at', '>=', $thisMonthStart)
                ->sum('amount');

            $thisMonthSpending = Transaction::where('user_id', $user->id)
                ->where('type', 'purchase')
                ->where('status', 'completed')
                ->where('created_at', '>=', $thisMonthStart)
                ->sum('amount');

            // Pending withdrawal requests
            $pendingWithdrawals = WithdrawalRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->sum('amount');

            // Recent transactions (last 5)
            $recentTransactions = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Format recent transactions data
            $formattedRecentTransactions = $recentTransactions->map(function ($transaction) {
                return [
                    'id' => (string)$transaction->id,
                    'type' => $transaction->type,
                    'amount' => (int)$transaction->amount,
                    'description' => $transaction->description,
                    'createdAt' => $transaction->created_at->toISOString()
                ];
            });

            // Earnings breakdown by source
            $earningsBreakdown = Transaction::where('user_id', $user->id)
                ->where('type', 'earning')
                ->where('status', 'completed')
                ->selectRaw('reference_type, SUM(amount) as total_amount, COUNT(*) as count')
                ->groupBy('reference_type')
                ->get()
                ->map(function ($item) {
                    $sourceName = match($item->reference_type) {
                        'App\Models\Advertisement' => 'Advertisement Views',
                        'App\Models\Product' => 'Product Sales',
                        'App\Models\Course' => 'Course Sales',
                        'App\Models\BrainTeaser' => 'Brain Teaser Rewards',
                        default => 'Other'
                    };

                    return [
                        'source' => $sourceName,
                        'totalAmount' => (int)$item->total_amount,
                        'transactionCount' => (int)$item->count
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'currentBalance' => (int)$currentBalance,
                    'availableBalance' => (int)($currentBalance - $pendingWithdrawals),
                    'pendingWithdrawals' => (int)$pendingWithdrawals,
                    'lifetimeStats' => [
                        'totalEarnings' => (int)$totalEarnings,
                        'totalSpending' => (int)$totalSpending,
                        'totalWithdrawals' => (int)$totalWithdrawals,
                        'netBalance' => (int)($totalEarnings - $totalSpending - $totalWithdrawals)
                    ],
                    'thisMonthStats' => [
                        'earnings' => (int)$thisMonthEarnings,
                        'spending' => (int)$thisMonthSpending,
                        'net' => (int)($thisMonthEarnings - $thisMonthSpending)
                    ],
                    'earningsBreakdown' => $earningsBreakdown,
                    'recentTransactions' => $formattedRecentTransactions
                ],
                'message' => 'Wallet summary retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wallet summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create withdrawal request
     */
    public function requestWithdrawal(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user has withdrawal access enabled by admin
            if (!$user->hasWithdrawalAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Withdrawal access has been disabled by admin. Please contact support for assistance.'
                ], 403);
            }
            
            // Check if user has bound bank account details
            if (!$user->hasBoundBankAccount()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must bind your bank account details before requesting a withdrawal.'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:10|max:10000',
                'payment_method_id' => 'required|integer|in:1,2' // 1 = wallet balance, 2 = referral earnings
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $amount = $request->amount;
            $paymentMethodId = $request->payment_method_id;

            // Check if user has sufficient balance based on payment method
            if ($paymentMethodId == 1) {
                // Withdraw from wallet balance
                $availableBalance = $user->wallet_balance;
                
                if ($availableBalance < $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance'
                    ], 400);
                }
            } else if ($paymentMethodId == 2) {
                // Withdraw from referral earnings
                $availableBalance = $user->referral_earnings;
                
                if ($availableBalance < $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient referral earnings balance'
                    ], 400);
                }
            }

            // Daily withdrawal limit has been removed - withdrawals are now unlimited

            // Map payment method ID to payment method name
            $paymentMethodName = $paymentMethodId == 1 ? 'Wallet Balance' : 'Referral Earnings';

            // Prepare account details from user's stored bank information
            $accountDetails = [
                'accountName' => $user->bank_account_holder_name,
                'accountNumber' => $user->bank_account_number,
                'bankName' => $user->bank_name
            ];

            // Deduct amount from user's balance immediately when creating withdrawal request
            DB::beginTransaction();
            try {
                if ($paymentMethodId == 1) {
                    // Deduct from wallet balance
                    $user->deductFromWallet($amount);
                } else if ($paymentMethodId == 2) {
                    // Deduct from referral earnings
                    $user->deductFromReferralEarnings($amount);
                }

                // Create withdrawal request
                $withdrawalRequest = WithdrawalRequest::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'payment_method' => $paymentMethodName,
                    'payment_method_id' => $paymentMethodId,
                    'payment_details' => $accountDetails,
                    'status' => 'pending'
                ]);

                // Create transaction record for the deduction
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal_request',
                    'amount' => -$amount, // Negative amount for deductions
                    'description' => 'Withdrawal requested - ' . $paymentMethodName,
                    'status' => 'pending',
                    'reference_type' => WithdrawalRequest::class,
                    'reference_id' => $withdrawalRequest->id
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            // Format withdrawal request data to match documentation
            $formattedWithdrawalRequest = [
                'id' => (string)$withdrawalRequest->id,
                'userId' => (string)$withdrawalRequest->user_id,
                'amount' => (int)$withdrawalRequest->amount,
                'currency' => 'NGN',
                'paymentMethod' => [
                    'id' => $paymentMethodId,
                    'name' => $paymentMethodName
                ],
                'accountDetails' => $withdrawalRequest->payment_details,
                'status' => $withdrawalRequest->status,
                'requestedAt' => $withdrawalRequest->created_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted successfully',
                'data' => ['withdrawal' => $formattedWithdrawalRequest]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create withdrawal request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's withdrawal requests
     */
    public function withdrawalRequests(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = WithdrawalRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $total = $query->count();
            $withdrawals = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format withdrawals data to match documentation
            $formattedWithdrawals = $withdrawals->map(function ($withdrawal) {
                // Get associated transaction
                $transaction = Transaction::where('reference_type', WithdrawalRequest::class)
                    ->where('reference_id', $withdrawal->id)
                    ->first();
                
                $result = [
                    'id' => (string)$withdrawal->id,
                    'userId' => (string)$withdrawal->user_id,
                    'amount' => (float)$withdrawal->amount,
                    'currency' => 'NGN',
                    'paymentMethod' => [
                        'id' => (int)$withdrawal->payment_method_id,
                        'name' => $withdrawal->payment_method
                    ],
                    'accountDetails' => $withdrawal->payment_details,
                    'status' => $withdrawal->status,
                    'requestedAt' => $withdrawal->created_at->toISOString(),
                    'processedAt' => $withdrawal->processed_at ? $withdrawal->processed_at->toISOString() : null,
                    'notes' => $withdrawal->notes,
                    'rejectionReason' => $withdrawal->rejection_reason,
                    'transactionId' => $withdrawal->transaction_id,
                    'createdAt' => $withdrawal->created_at->toISOString(),
                    'updatedAt' => $withdrawal->updated_at->toISOString()
                ];
                
                // Add transaction details if available
                if ($transaction) {
                    $result['transaction'] = [
                        'id' => (string)$transaction->id,
                        'transactionId' => $transaction->transaction_id,
                        'type' => $transaction->type,
                        'amount' => (float)$transaction->amount,
                        'description' => $transaction->description,
                        'status' => $transaction->status,
                        'createdAt' => $transaction->created_at->toISOString(),
                        'updatedAt' => $transaction->updated_at->toISOString()
                    ];
                }
                
                return $result;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'withdrawals' => $formattedWithdrawals
                ],
                'meta' => [
                    'pagination' => [
                        'total' => $total,
                        'count' => $formattedWithdrawals->count(),
                        'per_page' => (int)$limit,
                        'current_page' => (int)$page,
                        'total_pages' => (int)ceil($total / $limit)
                    ]
                ],
                'message' => 'Withdrawals retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch withdrawal requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel withdrawal request
     */
    public function cancelWithdrawal($id): JsonResponse
    {
        try {
            $user = Auth::user();

            $withdrawalRequest = WithdrawalRequest::where('id', $id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$withdrawalRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Withdrawal request not found or cannot be cancelled'
                ], 404);
            }

            $withdrawalRequest->update([
                'status' => 'cancelled',
                'processed_at' => now(),
                'admin_notes' => 'Cancelled by user'
            ]);

            // Format withdrawal request data to match documentation
            $formattedWithdrawalRequest = [
                'id' => (string)$withdrawalRequest->id,
                'userId' => (string)$withdrawalRequest->user_id,
                'amount' => (int)$withdrawalRequest->amount,
                'currency' => 'NGN',
                'paymentMethod' => $withdrawalRequest->payment_method,
                'paymentDetails' => $withdrawalRequest->payment_details,
                'status' => $withdrawalRequest->status,
                'processedAt' => $withdrawalRequest->processed_at ? $withdrawalRequest->processed_at->toISOString() : null,
                'adminNotes' => $withdrawalRequest->admin_notes,
                'createdAt' => $withdrawalRequest->created_at->toISOString(),
                'updatedAt' => $withdrawalRequest->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request cancelled successfully',
                'data' => ['withdrawal' => $formattedWithdrawalRequest]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel withdrawal request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add funds to wallet (for testing or admin purposes)
     */
    public function addFunds(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1|max:1000',
                'description' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $amount = $request->amount;
            $description = $request->description ?? 'Wallet top-up';

            DB::beginTransaction();

            try {
                // Add to wallet
                $user->addToWallet($amount);

                // Create transaction record
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'earning',
                    'amount' => $amount,
                    'description' => $description,
                    'status' => 'completed',
                    'reference_type' => null,
                    'reference_id' => null,
                    'metadata' => [
                        'source' => 'wallet_topup',
                        'method' => 'manual'
                    ]
                ]);

                DB::commit();

                // Format transaction data to match documentation
                $formattedTransaction = [
                    'id' => (string)$transaction->id,
                    'userId' => (string)$transaction->user_id,
                    'type' => $transaction->type,
                    'amount' => (int)$transaction->amount,
                    'currency' => 'NGN',
                    'description' => $transaction->description,
                    'status' => $transaction->status,
                    'referenceType' => $transaction->reference_type,
                    'referenceId' => $transaction->reference_id ? (string)$transaction->reference_id : null,
                    'createdAt' => $transaction->created_at->toISOString(),
                    'updatedAt' => $transaction->updated_at->toISOString()
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Funds added successfully',
                    'data' => [
                        'transaction' => $formattedTransaction,
                        'amountAdded' => (int)$amount,
                        'newBalance' => (int)$user->fresh()->wallet_balance
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add funds',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction statistics for charts/analytics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $period = $request->get('period', '30'); // days

            $startDate = Carbon::now()->subDays($period);

            // Daily transaction volumes
            $dailyStats = Transaction::where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, type, SUM(amount) as total_amount, COUNT(*) as count')
                ->groupBy('date', 'type')
                ->orderBy('date')
                ->get()
                ->groupBy('date')
                ->map(function ($transactions, $date) {
                    $earnings = $transactions->where('type', 'earning')->sum('total_amount');
                    $spending = $transactions->where('type', 'purchase')->sum('total_amount');
                    $withdrawals = $transactions->where('type', 'withdrawal')->sum('total_amount');

                    return [
                        'date' => $date,
                        'earnings' => (int)$earnings,
                        'spending' => (int)$spending,
                        'withdrawals' => (int)$withdrawals,
                        'net' => (int)($earnings - $spending - $withdrawals)
                    ];
                })
                ->values();

            // Transaction type distribution
            $typeDistribution = Transaction::where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->selectRaw('type, SUM(amount) as total_amount, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->map(function ($item) {
                    return [
                        'type' => $item->type,
                        'totalAmount' => (int)$item->total_amount,
                        'count' => (int)$item->count
                    ];
                });

            // Monthly comparison
            $thisMonth = Transaction::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->selectRaw('type, SUM(amount) as total_amount')
                ->groupBy('type')
                ->pluck('total_amount', 'type')
                ->toArray();

            $lastMonth = Transaction::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->whereYear('created_at', Carbon::now()->subMonth()->year)
                ->selectRaw('type, SUM(amount) as total_amount')
                ->groupBy('type')
                ->pluck('total_amount', 'type')
                ->toArray();

            // Format monthly comparison data
            $formattedThisMonth = [];
            foreach ($thisMonth as $type => $amount) {
                $formattedThisMonth[] = [
                    'type' => $type,
                    'amount' => (int)$amount
                ];
            }

            $formattedLastMonth = [];
            foreach ($lastMonth as $type => $amount) {
                $formattedLastMonth[] = [
                    'type' => $type,
                    'amount' => (int)$amount
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'periodDays' => (int)$period,
                    'dailyStats' => $dailyStats,
                    'typeDistribution' => $typeDistribution,
                    'monthlyComparison' => [
                        'thisMonth' => $formattedThisMonth,
                        'lastMonth' => $formattedLastMonth
                    ]
                ],
                'message' => 'Statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export transaction history
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'format' => 'required|in:csv,excel',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'type' => 'nullable|in:earning,purchase,withdrawal'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            $query = Transaction::where('user_id', $user->id);

            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->type) {
                $query->where('type', $request->type);
            }

            $transactions = $query->orderBy('created_at', 'desc')->get();

            // Format transactions data for export
            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'id' => (string)$transaction->id,
                    'type' => $transaction->type,
                    'amount' => (int)$transaction->amount,
                    'currency' => 'NGN',
                    'description' => $transaction->description,
                    'status' => $transaction->status,
                    'reference_type' => $transaction->reference_type,
                    'reference_id' => $transaction->reference_id ? (string)$transaction->reference_id : null,
                    'created_at' => $transaction->created_at->toISOString()
                ];
            });

            // For now, return the data as JSON
            // In a real implementation, you would generate CSV/Excel files
            return response()->json([
                'success' => true,
                'message' => 'Export data generated successfully',
                'data' => [
                    'format' => $request->format,
                    'totalRecords' => $formattedTransactions->count(),
                    'transactions' => $formattedTransactions->take(1000) // Limit for demo
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}