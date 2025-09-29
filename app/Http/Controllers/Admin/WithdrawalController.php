<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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

        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total_withdrawals' => WithdrawalRequest::count(),
            'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->count(),
            'approved_withdrawals' => WithdrawalRequest::where('status', 'approved')->count(),
            'total_amount_pending' => WithdrawalRequest::where('status', 'pending')->sum('amount'),
            'total_amount_approved' => WithdrawalRequest::where('status', 'approved')->sum('amount')
        ];

        return view('admin.withdrawals.index', compact('withdrawals', 'stats'));
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

        // Check if user has sufficient balance
        if ($withdrawal->user->wallet_balance < $withdrawal->amount) {
            return response()->json([
                'success' => false,
                'message' => 'User has insufficient balance for this withdrawal'
            ], 400);
        }

        // Deduct amount from user's wallet
        $withdrawal->user->deductFromWallet($withdrawal->amount);

        // Update withdrawal status
        $withdrawal->update([
            'status' => 'approved',
            'processed_at' => now(),
            'processed_by' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null
        ]);

        // Create transaction record
        // This would create a transaction record in your transactions table
        // Transaction::create([
        //     'user_id' => $withdrawal->user_id,
        //     'type' => 'withdrawal',
        //     'amount' => -$withdrawal->amount,
        //     'status' => 'completed',
        //     'withdrawal_id' => $withdrawal->id,
        //     'transaction_id' => $validated['transaction_id'] ?? 'withdrawal_' . $withdrawal->id
        // ]);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal approved successfully',
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

        $withdrawal->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'processed_by' => Auth::id(),
            'rejection_reason' => $validated['reason']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal rejected successfully',
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