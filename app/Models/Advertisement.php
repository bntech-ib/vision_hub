<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Advertisement model
 */
class Advertisement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'image_url',
        'target_url',
        'type',
        'reward_amount',
        'max_views',
        'current_views',
        'targeting',
        'start_date',
        'end_date',
        'status',
        'advertiser_id',
        'category',
        'budget',
        'spent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'targeting' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'reward_amount' => 'decimal:2',
        'budget' => 'decimal:2',
        'spent' => 'decimal:2'
    ];

    /**
     * Check if the advertisement is currently active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        // Check if status is active
        if ($this->status !== 'active') {
            return false;
        }

        // Check if current date is within the start and end dates
        $now = now();
        
        // Check if start date has passed
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }
        
        // Check if end date has passed (if set)
        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }
        
        // Check if ad spend has reached or exceeded budget
        if ($this->budget > 0 && $this->spent >= $this->budget) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the advertiser who owns this advertisement.
     */
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    /**
     * Get the ad interactions for this advertisement.
     */
    public function adInteractions(): HasMany
    {
        return $this->hasMany(AdInteraction::class);
    }

    /**
     * Alias for adInteractions to maintain compatibility with code expecting 'interactions' relationship
     */
    public function interactions(): HasMany
    {
        return $this->adInteractions();
    }
}