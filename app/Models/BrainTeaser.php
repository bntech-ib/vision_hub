<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrainTeaser extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'question',
        'options',
        'correct_answer',
        'explanation',
        'category',
        'difficulty',
        'reward_amount',
        'start_date',
        'end_date',
        'is_daily',
        'status',
        'total_attempts',
        'correct_attempts',
        'created_by'
    ];

    protected $casts = [
        'options' => 'array',
        'reward_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_daily' => 'boolean',
        'total_attempts' => 'integer',
        'correct_attempts' => 'integer'
    ];

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get attempts for this brain teaser
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(BrainTeaserAttempt::class);
    }

    /**
     * Check if answer is correct
     */
    public function isCorrectAnswer(string $answer): bool
    {
        return strtolower(trim($answer)) === strtolower(trim($this->correct_answer));
    }

    /**
     * Check if brain teaser is currently active
     */
    public function isActive(): bool
    {
        // Check if status is active
        if ($this->status !== 'active') {
            return false;
        }

        // Check date range if start_date and end_date are set
        $now = now();
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }
}