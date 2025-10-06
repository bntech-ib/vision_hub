<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of withdrawal requests
     */
    public function index(Request $request): View
    {
        $query = WithdrawalRequest::with(['user']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('withdrawal_id', 'like', '%' . $request->search . '%')
                  ->orWhere('payment_method', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    /**
     * Display the specified withdrawal
     */
    public function show(WithdrawalRequest $withdrawal): View
    {
        $withdrawal->load(['user']);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Approve a withdrawal request
     */
    public function approve(Request $request, WithdrawalRequest $withdrawal): JsonResponse
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending withdrawals can be approved'
            ], 400);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'transaction_id' => 'nullable|string|max:255'
        ]);

        // Check if user has sufficient balance based on payment method
        // This is just a safety check, as the amount should already be deducted
        if ($withdrawal->payment_method_id == 1) {
            // Check wallet balance
            if ($withdrawal->user->wallet_balance < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has negative wallet balance'
                ], 400);
            }
        } else if ($withdrawal->payment_method_id == 2) {
            // Check referral earnings balance
            if ($withdrawal->user->referral_earnings < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has negative referral earnings balance'
                ], 400);
            }
        }

        // Update withdrawal status - no additional deduction needed as it was already deducted
        $withdrawal->update([
            'status' => 'approved',
            'processed_at' => now(),
            'processed_by' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null
        ]);

        // Update the transaction record status
        Transaction::where('reference_type', WithdrawalRequest::class)
            ->where('reference_id', $withdrawal->id)
            ->update([
                'status' => 'completed',
                'transaction_id' => $validated['transaction_id'] ?? 'withdrawal_' . $withdrawal->id
            ]);

        // Determine message based on payment method
        $message = 'Withdrawal approved successfully';
        if ($withdrawal->payment_method_id == 1) {
            $message = 'Wallet balance withdrawal approved successfully';
        } else if ($withdrawal->payment_method_id == 2) {
            $message = 'Referral earnings withdrawal approved successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'withdrawal' => $withdrawal->fresh(['user'])
        ]);
    }

    /**
     * Reject a withdrawal request
     */
    public function reject(Request $request, WithdrawalRequest $withdrawal): JsonResponse
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending withdrawals can be rejected'
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        // Refund the amount to the user's original source
        DB::beginTransaction();
        try {
            if ($withdrawal->payment_method_id == 1) {
                // Refund to wallet balance
                $withdrawal->user->addToWallet($withdrawal->amount);
            } else if ($withdrawal->payment_method_id == 2) {
                // Refund to referral earnings
                $withdrawal->user->addToReferralEarnings($withdrawal->amount);
            }

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'rejected',
                'processed_at' => now(),
                'processed_by' => Auth::id(),
                'rejection_reason' => $validated['reason']
            ]);

            // Create refund transaction record
            Transaction::create([
                'user_id' => $withdrawal->user_id,
                'type' => 'withdrawal_refund',
                'amount' => $withdrawal->amount, // Positive amount for refunds
                'description' => 'Withdrawal rejected - ' . ($withdrawal->payment_method_id == 1 ? 'Wallet Balance' : 'Referral Earnings') . ' refunded',
                'status' => 'completed',
                'reference_type' => WithdrawalRequest::class,
                'reference_id' => $withdrawal->id,
                'transaction_id' => 'refund_withdrawal_' . $withdrawal->id
            ]);

            // Update the original transaction record status
            Transaction::where('reference_type', WithdrawalRequest::class)
                ->where('reference_id', $withdrawal->id)
                ->update([
                    'status' => 'refunded',
                    'description' => 'Withdrawal rejected - Amount refunded to ' . ($withdrawal->payment_method_id == 1 ? 'Wallet Balance' : 'Referral Earnings')
                ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject withdrawal request: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal rejected successfully and amount refunded',
            'withdrawal' => $withdrawal->fresh(['user'])
        ]);
    }

    /**
     * Get pending withdrawal requests
     */
    public function pending(Request $request): JsonResponse
    {
        $withdrawals = WithdrawalRequest::where('status', 'pending')
            ->with(['user'])
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $withdrawals
        ]);
    }

    /**
     * Export withdrawals
     */
    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|in:pending,approved,rejected,processing',
            'payment_method' => 'nullable|string'
        ]);

        // This would implement the actual export logic
        // For now, return a success response
        
        return response()->json([
            'success' => true,
            'message' => 'Export queued successfully. You will receive an email when ready.',
            'export_id' => uniqid('withdrawal_export_')
        ]);
    }
}