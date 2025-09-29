<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index(Request $request): View
    {
        $query = Transaction::with(['user']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_id', 'like', '%' . $request->search . '%')
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

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $users = User::select('id', 'name', 'email')->get();

        return view('admin.transactions.index', compact('transactions', 'users'));
    }

    /**
     * Display the specified transaction
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load(['user']);

        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Refund a transaction
     */
    public function refund(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed transactions can be refunded'
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'amount' => 'nullable|numeric|min:0.01|max:' . $transaction->amount
        ]);

        $refundAmount = $validated['amount'] ?? $transaction->amount;

        // Create refund transaction
        $refund = Transaction::create([
            'user_id' => $transaction->user_id,
            'type' => 'refund',
            'amount' => -$refundAmount,
            'status' => 'completed',
            'payment_method' => $transaction->payment_method,
            'transaction_id' => 'refund_' . $transaction->transaction_id,
            'metadata' => [
                'original_transaction_id' => $transaction->id,
                'reason' => $validated['reason'],
                'refunded_by' => Auth::id()
            ]
        ]);

        // Update original transaction
        $transaction->update([
            'status' => $refundAmount >= $transaction->amount ? 'refunded' : 'partial_refund',
            'metadata' => array_merge($transaction->metadata ?? [], [
                'refund_transaction_id' => $refund->id,
                'refund_amount' => $refundAmount,
                'refund_reason' => $validated['reason']
            ])
        ]);

        // Add amount back to user's wallet
        $transaction->user->addToWallet($refundAmount);

        return response()->json([
            'success' => true,
            'message' => 'Transaction refunded successfully',
            'refund' => $refund
        ]);
    }

    /**
     * Export transactions
     */
    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|in:pending,completed,failed,refunded,partial_refund',
            'type' => 'nullable|in:payment,refund,withdrawal'
        ]);

        // This would implement the actual export logic
        // For now, return a success response
        
        return response()->json([
            'success' => true,
            'message' => 'Export queued successfully. You will receive an email when ready.',
            'export_id' => uniqid('export_')
        ]);
    }

    /**
     * Get revenue summary
     */
    public function revenueSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'required|in:today,week,month,quarter,year',
            'compare' => 'nullable|boolean'
        ]);

        $period = $validated['period'];
        $compare = $validated['compare'] ?? false;

        $data = [
            'current' => $this->getRevenuePeriod($period),
            'previous' => $compare ? $this->getRevenuePeriod($period, true) : null,
            'breakdown' => $this->getRevenueBreakdown($period),
            'top_customers' => $this->getTopCustomers($period),
            'payment_methods' => $this->getPaymentMethodBreakdown($period)
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get revenue for a specific period
     */
    private function getRevenuePeriod(string $period, bool $previous = false): array
    {
        $query = Transaction::where('status', 'completed')
                           ->where('type', 'payment');

        switch ($period) {
            case 'today':
                $date = $previous ? Carbon::yesterday() : Carbon::today();
                $query->whereDate('created_at', $date);
                break;
            case 'week':
                if ($previous) {
                    $query->whereBetween('created_at', [
                        Carbon::now()->subWeeks(2)->startOfWeek(),
                        Carbon::now()->subWeek()->endOfWeek()
                    ]);
                } else {
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                }
                break;
            case 'month':
                if ($previous) {
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                          ->whereYear('created_at', Carbon::now()->subMonth()->year);
                } else {
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                }
                break;
            case 'quarter':
                if ($previous) {
                    $query->whereBetween('created_at', [
                        Carbon::now()->subQuarter()->firstOfQuarter(),
                        Carbon::now()->subQuarter()->lastOfQuarter()
                    ]);
                } else {
                    $query->whereBetween('created_at', [
                        Carbon::now()->firstOfQuarter(),
                        Carbon::now()->lastOfQuarter()
                    ]);
                }
                break;
            case 'year':
                $year = $previous ? Carbon::now()->subYear()->year : Carbon::now()->year;
                $query->whereYear('created_at', $year);
                break;
        }

        return [
            'total_revenue' => $query->sum('amount'),
            'transaction_count' => $query->count(),
            'average_transaction' => $query->avg('amount') ?: 0
        ];
    }

    /**
     * Get revenue breakdown by day/month
     */
    private function getRevenueBreakdown(string $period): array
    {
        $query = Transaction::where('status', 'completed')
                           ->where('type', 'payment');

        switch ($period) {
            case 'today':
            case 'week':
                $query->whereBetween('created_at', [
                    Carbon::now()->subDays(7),
                    Carbon::now()
                ]);
                $groupBy = 'DATE(created_at)';
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                $groupBy = 'DATE(created_at)';
                break;
            default:
                $query->whereYear('created_at', Carbon::now()->year);
                $groupBy = 'MONTH(created_at)';
        }

        return $query->selectRaw("{$groupBy} as period, SUM(amount) as revenue, COUNT(*) as count")
                     ->groupByRaw($groupBy)
                     ->orderBy('period')
                     ->get()
                     ->toArray();
    }

    /**
     * Get top customers for period
     */
    private function getTopCustomers(string $period): array
    {
        $query = Transaction::with('user')
                           ->where('status', 'completed')
                           ->where('type', 'payment');

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
        }

        return $query->selectRaw('user_id, SUM(amount) as total_spent, COUNT(*) as transaction_count')
                     ->groupBy('user_id')
                     ->orderByDesc('total_spent')
                     ->limit(10)
                     ->get()
                     ->toArray();
    }

    /**
     * Get payment method breakdown
     */
    private function getPaymentMethodBreakdown(string $period): array
    {
        $query = Transaction::where('status', 'completed')
                           ->where('type', 'payment');

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
        }

        return $query->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
                     ->groupBy('payment_method')
                     ->orderByDesc('total')
                     ->get()
                     ->toArray();
    }
}