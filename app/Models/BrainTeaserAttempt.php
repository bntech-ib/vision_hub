<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrainTeaserAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brain_teaser_id',
        'answer',
        'is_correct',
        'attempted_at',
        'reward_earned'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'reward_earned' => 'decimal:2',
        'attempted_at' => 'datetime'
    ];

    /**
     * Get the user who attempted the brain teaser
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the brain teaser that was attempted
     */
    public function brainTeaser(): BelongsTo
    {
        return $this->belongsTo(BrainTeaser::class);
    }

    /**
     * Check if the attempt was correct
     */
    public function wasCorrect(): bool
    {
        return $this->is_correct;
    }

    /**
     * Get the reward earned (0 if incorrect)
     */
    public function getRewardEarned(): float
    {
        return $this->is_correct ? $this->reward_earned : 0;
    }
}