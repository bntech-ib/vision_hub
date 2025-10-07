<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPackage extends Package
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'features',
        'ad_views_limit',
        'course_access_limit',
        'marketplace_access',
        'brain_teaser_access',
        'duration_days',
        'is_active',
        'referral_earning_percentage',
        'welcome_bonus',
        'daily_earning_limit',
        'ad_limits'
    ];

    protected $casts = [
        'features' => 'array',
        'marketplace_access' => 'boolean',
        'brain_teaser_access' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'referral_earning_percentage' => 'decimal:2',
        'welcome_bonus' => 'decimal:2',
        'daily_earning_limit' => 'decimal:2'
    ];

    /**
     * Get users who have this package
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'current_package_id');
    }

    /**
     * Get access keys for this package
     */
    public function accessKeys(): HasMany
    {
        return $this->hasMany(AccessKey::class, 'package_id');
    }
    
    /**
     * Calculate referral earning amount based on this package's fixed amount
     * If referral_earning_percentage is set and greater than 0, use it as a fixed amount
     * Otherwise, return the base amount (fallback)
     */
    public function calculateReferralEarning(float $baseAmount): float
    {
        // If referral_earning_percentage is set and greater than 0, use it as a fixed amount
        if (isset($this->referral_earning_percentage) && $this->referral_earning_percentage !== null && $this->referral_earning_percentage > 0) {
            return (float) $this->referral_earning_percentage;
        }
        
        // Otherwise, return the base amount (fallback)
        return $baseAmount;
    }
    
    /**
     * Calculate earning per ad interaction based on daily earning limit and ad limits
     */
    public function calculateEarningPerAd(): float
    {
        // For both limited and unlimited packages, use the same formula
        // If ad_limits is 0 (unlimited), division by zero would occur
        // To avoid this, we'll use a very small number instead of 0 for unlimited packages
        $adLimits = $this->ad_limits > 0 ? $this->ad_limits : 1;
        
        // Calculate earning per ad = daily earning limit / ad limits
        return $this->daily_earning_limit / $adLimits;
    }
}