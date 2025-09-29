<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'advertisement_id',
        'type',
        'reward_earned',
        'metadata',
        'interacted_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'interacted_at' => 'datetime',
        'reward_earned' => 'decimal:2'
    ];

    /**
     * Get the user who interacted
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the advertisement
     */
    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }
}