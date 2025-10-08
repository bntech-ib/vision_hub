<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'payment_method_id',
        'payment_details',
        'status',
        'processed_at',
        'admin_note',
        'notes',
        'transaction_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_method_id' => 'integer',
        'payment_details' => 'array',
        'processed_at' => 'datetime'
    ];

    /**
     * Get the user who requested the withdrawal
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the withdrawal request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the withdrawal request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the withdrawal request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the withdrawal request is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Approve the withdrawal request
     */
    public function approve(string $adminNote = null): void
    {
        $this->update([
            'status' => 'approved',
            'processed_at' => now(),
            'admin_note' => $adminNote
        ]);
    }

    /**
     * Reject the withdrawal request
     */
    public function reject(string $adminNote = null): void
    {
        $this->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'admin_note' => $adminNote
        ]);
    }

    /**
     * Mark withdrawal as completed
     */
    public function markCompleted(string $adminNote = null): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'admin_note' => $adminNote
        ]);
    }
}