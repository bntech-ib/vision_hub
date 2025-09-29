<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'target_url',
        'category',
        'budget',
        'reward_amount',
        'spent',
        'impressions',
        'clicks',
        'start_date',
        'end_date',
        'status',
        'advertiser_id',
        'targeting'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
        'reward_amount' => 'decimal:2',
        'spent' => 'decimal:2',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'targeting' => 'array'
    ];

    /**
     * Get the user who created this ad (advertiser)
     */
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    /**
     * Get ad interactions
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(AdInteraction::class);
    }

    /**
     * Get ad images
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'advertisement_image');
    }

    /**
     * Check if ad is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->start_date <= now() 
            && ($this->end_date === null || $this->end_date >= now());
    }
}