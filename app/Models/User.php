<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

// Import related models
use App\Models\Project;
use App\Models\Image;
use App\Models\ProcessingJob;
use App\Models\Tag;
use App\Models\Advertisement;
use App\Models\AdInteraction;
use App\Models\Product;
use App\Models\Course;
use App\Models\BrainTeaser;
use App\Models\Transaction;
use App\Models\UserPackage;
use App\Models\SecurityLog;
use App\Models\AccessKey;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'full_name',
        'email',
        'phone',
        'country',
        'profile_image',
        'referral_code',
        'password',
        'current_package_id',
        'package_expires_at',
        'wallet_balance',
        'is_admin',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'referred_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'package_expires_at' => 'datetime',
            'wallet_balance' => 'decimal:2',
            'is_admin' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    /**
     * Get the current package
     */
    public function currentPackage(): BelongsTo
    {
        return $this->belongsTo(UserPackage::class, 'current_package_id');
    }

    /**
     * Get the projects for the user
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the images uploaded by the user
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'uploaded_by');
    }

    /**
     * Get the processing jobs created by the user
     */
    public function processingJobs(): HasMany
    {
        return $this->hasMany(ProcessingJob::class);
    }

    /**
     * Get the tags created by the user
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class, 'created_by');
    }

    /**
     * Get user's advertisements
     */
    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class, 'advertiser_id');
    }

    /**
     * Get user's ad interactions
     */
    public function adInteractions(): HasMany
    {
        return $this->hasMany(AdInteraction::class);
    }

    /**
     * Get user's products
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * Get user's courses as instructor
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * Get user's course enrollments
     */
    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Get user's brain teasers
     */
    public function brainTeasers(): HasMany
    {
        return $this->hasMany(BrainTeaser::class, 'created_by');
    }

    /**
     * Get user's brain teaser attempts
     */
    public function brainTeaserAttempts(): HasMany
    {
        return $this->hasMany(BrainTeaserAttempt::class);
    }

    /**
     * Get user's transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get user's withdrawal requests
     */
    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    /**
     * Get referral bonuses earned by this user
     */
    public function referralBonuses(): HasMany
    {
        return $this->hasMany(ReferralBonus::class, 'referrer_id');
    }

    /**
     * Get the referral bonus record for this user (if this user was referred)
     */
    public function referralBonus(): HasOne
    {
        return $this->hasOne(ReferralBonus::class, 'referred_user_id');
    }

    /**
     * Get access keys created by this user (admin only)
     */
    public function createdAccessKeys(): HasMany
    {
        return $this->hasMany(AccessKey::class, 'created_by');
    }

    /**
     * Get sponsored posts created by this user
     */
    public function sponsoredPosts(): HasMany
    {
        return $this->hasMany(SponsoredPost::class);
    }

    /**
     * Get users referred by this user
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Get the user who referred this user
     */
    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Get level 1 referrals (direct referrals)
     */
    public function referralsLevel1(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Get level 2 referrals (indirect referrals)
     */
    public function referralsLevel2()
    {
        return User::whereIn('referred_by', $this->referralsLevel1->pluck('id'));
    }

    /**
     * Get level 3 referrals (second indirect referrals)
     */
    public function referralsLevel3()
    {
        $level2Ids = $this->referralsLevel2()->pluck('id');
        return User::whereIn('referred_by', $level2Ids);
    }

    /**
     * Get all referrals up to 3 levels deep
     * 
     * @return array{
     *     level1: \Illuminate\Database\Eloquent\Collection,
     *     level2: \Illuminate\Database\Eloquent\Collection,
     *     level3: \Illuminate\Database\Eloquent\Collection
     * }
     */
    public function getAllReferrals()
    {
        $level1 = $this->referralsLevel1;
        $level2 = $this->referralsLevel2()->get();
        $level3 = $this->referralsLevel3()->get();

        return [
            'level1' => $level1,
            'level2' => $level2,
            'level3' => $level3,
        ];
    }

    /**
     * Get referral statistics
     * 
     * @return array{
     *     level1_count: int,
     *     level2_count: int,
     *     level3_count: int,
     *     total_count: int
     * }
     */
    public function getReferralStats()
    {
        $referrals = $this->getAllReferrals();
        
        return [
            'level1_count' => $referrals['level1']->count(),
            'level2_count' => $referrals['level2']->count(),
            'level3_count' => $referrals['level3']->count(),
            'total_count' => $referrals['level1']->count() + $referrals['level2']->count() + $referrals['level3']->count(),
        ];
    }

    /**
     * Get referral earnings by level
     * 
     * @return array{
     *     level1: float,
     *     level2: float,
     *     level3: float,
     *     total: float
     * }
     */
    public function getReferralEarningsByLevel()
    {
        $level1Earnings = $this->referralBonuses()->where('level', 1)->sum('amount');
        $level2Earnings = $this->referralBonuses()->where('level', 2)->sum('amount');
        $level3Earnings = $this->referralBonuses()->where('level', 3)->sum('amount');
        
        return [
            'level1' => $level1Earnings,
            'level2' => $level2Earnings,
            'level3' => $level3Earnings,
            'total' => $level1Earnings + $level2Earnings + $level3Earnings,
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Check if user has active package
     */
    public function hasActivePackage(): bool
    {
        return $this->current_package_id && 
               ($this->package_expires_at === null || $this->package_expires_at->isFuture());
    }

    /**
     * Get active package
     */
    public function activePackage(): ?UserPackage
    {
        if ($this->hasActivePackage()) {
            return $this->currentPackage;
        }

        return null;
    }

    /**
     * Add to wallet balance
     */
    public function addToWallet(float $amount): void
    {
        $this->increment('wallet_balance', $amount);
    }

    /**
     * Deduct from wallet balance
     */
    public function deductFromWallet(float $amount): bool
    {
        if ($this->wallet_balance >= $amount) {
            $this->decrement('wallet_balance', $amount);
            return true;
        }
        return false;
    }

    /**
     * Enable 2FA for the user
     */
    public function enableTwoFactorAuthentication(): void
    {
        $this->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => Str::random(32),
        ])->save();
    }

    /**
     * Disable 2FA for the user
     */
    public function disableTwoFactorAuthentication(): void
    {
        $this->forceFill([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    /**
     * Confirm 2FA for the user
     */
    public function confirmTwoFactorAuthentication(): void
    {
        $this->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();
    }

    /**
     * Generate recovery codes for the user
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10);
        }

        $this->forceFill([
            'two_factor_recovery_codes' => Crypt::encrypt(json_encode($codes)),
        ])->save();

        return $codes;
    }

    /**
     * Get recovery codes for the user
     */
    public function getRecoveryCodes(): array
    {
        if (!$this->two_factor_recovery_codes) {
            return [];
        }

        try {
            return json_decode(Crypt::decrypt($this->two_factor_recovery_codes), true);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Use a recovery code
     */
    public function useRecoveryCode(string $code): bool
    {
        $recoveryCodes = $this->getRecoveryCodes();

        if (in_array($code, $recoveryCodes)) {
            $recoveryCodes = array_diff($recoveryCodes, [$code]);
            $this->forceFill([
                'two_factor_recovery_codes' => Crypt::encrypt(json_encode(array_values($recoveryCodes))),
            ])->save();

            return true;
        }

        return false;
    }

    /**
     * Check if 2FA is confirmed
     */
    public function hasConfirmedTwoFactor(): bool
    {
        return !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Get security logs for the user
     */
    public function securityLogs(): HasMany
    {
        return $this->hasMany(SecurityLog::class);
    }

    /**
     * Log a security event
     */
    public function logSecurityEvent(string $action, array $details = [], bool $successful = true): void
    {
        $request = request();
        
        SecurityLog::create([
            'user_id' => $this->id,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'location' => null, // Could integrate with IP geolocation service
            'successful' => $successful,
            'details' => json_encode($details),
        ]);
    }
    
    /**
     * Check if user has reached their ad view limit
     */
    public function hasReachedAdViewLimit(): bool
    {
        // If user doesn't have an active package, they can view ads
        if (!$this->hasActivePackage()) {
            return false;
        }
        
        // If ad_views_limit is 0 or null, it means unlimited
        $adViewLimit = $this->currentPackage->ad_views_limit;
        if (!$adViewLimit || $adViewLimit == 0) {
            return false;
        }
        
        // Count ad views for the current month
        $adViewsThisMonth = $this->adInteractions()
            ->where('type', 'view')
            ->whereMonth('interacted_at', now()->month)
            ->whereYear('interacted_at', now()->year)
            ->count();
            
        return $adViewsThisMonth >= $adViewLimit;
    }
    
    /**
     * Check if user has brain teaser access based on their package
     */
    public function hasBrainTeaserAccess(): bool
    {
        // If user doesn't have an active package, they don't have access
        if (!$this->hasActivePackage()) {
            return false;
        }
        
        // Check if the package allows brain teaser access
        return $this->currentPackage->brain_teaser_access ?? false;
    }
    
    /**
     * Check if user has withdrawal access enabled by admin
     */
    public function hasWithdrawalAccess(): bool
    {
        // Check global setting for withdrawal access
        return \App\Models\GlobalSetting::isWithdrawalEnabled();
    }
    
    /**
     * Get available ads for the user based on their package limit
     */
    public function getAvailableAdsQuery()
    {
        // Start with active ads
        $query = Advertisement::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
            
        // If user has an active package with ad view limit
        if ($this->hasActivePackage() && $this->currentPackage->ad_views_limit > 0) {
            // Previously, we filtered out ads that the user had already viewed
            // But now we want to show all ads, even those already viewed
            // So we're removing the filtering logic that excluded viewed ads
            
            // The following code was removed:
            // $viewedAdIds = $this->adInteractions()
            //     ->where('type', 'view')
            //     ->pluck('advertisement_id');
            //     
            // if ($viewedAdIds->isNotEmpty()) {
            //     $query->whereNotIn('id', $viewedAdIds);
            // }
        }
        
        return $query;
    }
    
    /**
     * Get available brain teasers for the user based on their package
     */
    public function getAvailableBrainTeasersQuery()
    {
        // Start with active brain teasers
        $query = BrainTeaser::where('status', 'active');
        
        // If user has brain teaser access through their package
        if ($this->hasBrainTeaserAccess()) {
            // Get brain teasers that user hasn't attempted yet
            $attemptedIds = $this->brainTeaserAttempts()
                ->pluck('brain_teaser_id');
                
            if ($attemptedIds->isNotEmpty()) {
                $query->whereNotIn('id', $attemptedIds);
            }
        } else {
            // If user doesn't have access, return empty query
            $query->where('id', 0); // This will return no results
        }
        
        return $query;
    }
}