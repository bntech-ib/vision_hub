<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
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
use App\Models\CourseEnrollment;
use App\Models\ReferralBonus;
use App\Models\WithdrawalRequest;
use App\Models\BrainTeaserAttempt;
use App\Models\SponsoredPost;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

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
        'welcome_bonus',
        'referral_earnings',
        'is_admin',
        'is_vendor',
        'vendor_company_name',
        'vendor_description',
        'vendor_website',
        'vendor_commission_rate',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'referred_by',
        // Bank account fields
        'bank_account_holder_name',
        'bank_account_number',
        'bank_name',
        'bank_branch',
        'bank_routing_number'
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
        // Hide bank account details for security
        'bank_account_number',
        'bank_routing_number',
        'bank_account_holder_name',
        'bank_name',
        'bank_branch'
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
            'welcome_bonus' => 'decimal:2',
            'referral_earnings' => 'decimal:2',
            'is_admin' => 'boolean',
            'is_vendor' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'bank_account_verified' => 'boolean',
            'bank_account_bound_at' => 'datetime'
        ];
    }

    /**
     * Get the decrypted bank account holder name.
     *
     * @return string|null
     */
    public function getBankAccountHolderNameAttribute($value)
    {
        // Return the value as-is (no encryption/decryption)
        return $value;
    }

    /**
     * Get the decrypted bank account number.
     *
     * @return string|null
     */
    public function getBankAccountNumberAttribute($value)
    {
        // Return the value as-is (no encryption/decryption)
        return $value;
    }

    /**
     * Get the decrypted bank name.
     *
     * @return string|null
     */
    public function getBankNameAttribute($value)
    {
        // Return the value as-is (no encryption/decryption)
        return $value;
    }

    /**
     * Get the decrypted bank branch.
     *
     * @return string|null
     */
    public function getBankBranchAttribute($value)
    {
        // Return the value as-is (no encryption/decryption)
        return $value;
    }

    /**
     * Get the decrypted bank routing number.
     *
     * @return string|null
     */
    public function getBankRoutingNumberAttribute($value)
    {
        // Return the value as-is (no encryption/decryption)
        return $value;
    }
    
    /**
     * Get bank account details for admin display
     * This method safely exposes decrypted bank account information for admin use
     *
     * @return array
     */
    public function getAdminBankAccountDetails(): array
    {
        return [
            'bank_account_holder_name' => $this->bank_account_holder_name,
            'bank_account_number' => $this->bank_account_number,
            'bank_name' => $this->bank_name,
            'bank_branch' => $this->bank_branch,
            'bank_routing_number' => $this->bank_routing_number,
            'bank_account_bound_at' => $this->bank_account_bound_at,
            'has_bound_bank_account' => $this->hasBoundBankAccount(),
        ];
    }
    
    /**
     * Get the last login time for the user
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getLastLoginAtAttribute()
    {
        $lastLogin = SecurityLog::where('user_id', $this->id)
            ->where('action', 'login_successful')
            ->orderBy('created_at', 'desc')
            ->first();
            
        return $lastLogin ? $lastLogin->created_at : null;
    }
    
    /**
     * Check if the user is active (logged in within the last 30 days)
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $lastLoginAt = $this->last_login_at;
        return $lastLoginAt && $lastLoginAt->gte(now()->subDays(30));
    }
    
    /**
     * Set the bank account holder name.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setBankAccountHolderNameAttribute($value)
    {
        // Store the value as-is (no encryption)
        $this->attributes['bank_account_holder_name'] = $value;
    }

    /**
     * Set the bank account number.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setBankAccountNumberAttribute($value)
    {
        // Store the value as-is (no encryption)
        $this->attributes['bank_account_number'] = $value;
    }

    /**
     * Set the bank name.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setBankNameAttribute($value)
    {
        // Store the value as-is (no encryption)
        $this->attributes['bank_name'] = $value;
    }

    /**
     * Set the bank branch.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setBankBranchAttribute($value)
    {
        // Store the value as-is (no encryption)
        $this->attributes['bank_branch'] = $value;
    }

    /**
     * Set the bank routing number.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setBankRoutingNumberAttribute($value)
    {
        // Store the value as-is (no encryption)
        $this->attributes['bank_routing_number'] = $value;
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
     * Get user's access keys
     */
    public function accessKeys(): HasMany
    {
        return $this->hasMany(AccessKey::class);
    }
    
    /**
     * Get access keys created by this user
     */
    public function createdAccessKeys(): HasMany
    {
        return $this->hasMany(AccessKey::class, 'created_by');
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
     * Add to wallet balance (normal earnings)
     */
    public function addToWallet(float $amount): void
    {
        $this->increment('wallet_balance', $amount);
    }

    /**
     * Add to welcome bonus
     */
    public function addToWelcomeBonus(float $amount): void
    {
        $this->increment('welcome_bonus', $amount);
    }

    /**
     * Add to referral earnings
     */
    public function addToReferralEarnings(float $amount): void
    {
        $this->increment('referral_earnings', $amount);
    }

    /**
     * Add to both wallet balance and referral earnings
     */
    public function addToWalletAndReferralEarnings(float $walletAmount, float $referralAmount): void
    {
        $this->increment('wallet_balance', $walletAmount);
        $this->increment('referral_earnings', $referralAmount);
    }

    /**
     * Add to wallet balance and welcome bonus
     */
    public function addToWalletAndWelcomeBonus(float $walletAmount, float $welcomeBonusAmount): void
    {
        $this->increment('wallet_balance', $walletAmount);
        $this->increment('welcome_bonus', $welcomeBonusAmount);
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
     * Deduct from welcome bonus
     */
    public function deductFromWelcomeBonus(float $amount): bool
    {
        if ($this->welcome_bonus >= $amount) {
            $this->decrement('welcome_bonus', $amount);
            return true;
        }
        return false;
    }

    /**
     * Deduct from referral earnings
     */
    public function deductFromReferralEarnings(float $amount): bool
    {
        if ($this->referral_earnings >= $amount) {
            $this->decrement('referral_earnings', $amount);
            return true;
        }
        return false;
    }

    /**
     * Get total earnings (wallet + referral earnings + welcome bonus)
     */
    public function getTotalEarnings(): float
    {
        return (float) $this->wallet_balance + (float) $this->referral_earnings + (float) $this->welcome_bonus;
    }

    /**
     * Get today's earnings
     */
    public function getTodayEarnings(): float
    {
        return (float) $this->transactions()
            ->whereIn('type', ['earning', 'referral_earning'])
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('amount');
    }

    /**
     * Get today's normal earnings (from ad interactions)
     */
    public function getTodayNormalEarnings(): float
    {
        return (float) $this->transactions()
            ->where('type', 'earning')
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('amount');
    }

    /**
     * Get today's referral earnings
     */
    public function getTodayReferralEarnings(): float
    {
        return (float) $this->transactions()
            ->where('type', 'referral_earning')
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('amount');
    }

    /**
     * Get normal earnings (wallet balance)
     */
    public function getNormalEarnings(): float
    {
        return (float) $this->wallet_balance;
    }

    /**
     * Get referral earnings
     */
    public function getReferralEarnings(): float
    {
        return (float) $this->referral_earnings;
    }

    /**
     * Get welcome bonus amount
     */
    public function getWelcomeBonus(): float
    {
        return (float) $this->welcome_bonus;
    }

    /**
     * Check if user has claimed their welcome bonus
     */
    public function hasClaimedWelcomeBonus(): bool
    {
        return $this->welcome_bonus > 0;
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
     * Get referral statistics
     */
    public function getReferralStats(): array
    {
        $level1Count = $this->referrals()->count();
        
        // Note: Level 2 and 3 referral earnings have been disabled
        // but we still count them for statistical purposes
        $level2Count = 0;
        $level3Count = 0;
        
        // Get level 2 referrals (referrals of referrals)
        $level1Referrals = $this->referrals()->pluck('id');
        if ($level1Referrals->isNotEmpty()) {
            $level2Count = User::whereIn('referred_by', $level1Referrals)->count();
            
            // Get level 3 referrals
            $level2Referrals = User::whereIn('referred_by', $level1Referrals)->pluck('id');
            if ($level2Referrals->isNotEmpty()) {
                $level3Count = User::whereIn('referred_by', $level2Referrals)->count();
            }
        }
        
        return [
            'level1_count' => $level1Count,
            'level2_count' => $level2Count,
            'level3_count' => $level3Count,
            'total_count' => $level1Count + $level2Count + $level3Count,
        ];
    }

    /**
     * Get detailed referral statistics including user information
     */
    public function getDetailedReferralStats(): array
    {
        // Get level 1 referrals with their package information
        $referrals = $this->referrals()
            ->with(['currentPackage'])
            ->get()
            ->map(function ($referral) {
                return [
                    'id' => $referral->id,
                    'username' => $referral->username,
                    'package_name' => $referral->currentPackage ? $referral->currentPackage->name : 'No Package',
                    'registered_at' => $referral->created_at->toISOString(),
                ];
            });

        return [
            'total_referrals' => $referrals->count(),
            'referrals' => $referrals->toArray(),
        ];
    }
    
    /**
     * Get referral earnings by level
     * Note: Level 2 and 3 referral earnings have been disabled as of the latest update
     */
    public function getReferralEarningsByLevel(): array
    {
        $level1Earnings = ReferralBonus::where('referrer_id', $this->id)
            ->where('level', 1)
            ->sum('amount');
            
        // Level 2 and 3 earnings are no longer awarded but we still track historical data
        $level2Earnings = ReferralBonus::where('referrer_id', $this->id)
            ->where('level', 2)
            ->sum('amount');
            
        $level3Earnings = ReferralBonus::where('referrer_id', $this->id)
            ->where('level', 3)
            ->sum('amount');
            
        return [
            'level1' => $level1Earnings,
            'level2' => $level2Earnings,
            'level3' => $level3Earnings,
            'total' => $level1Earnings + $level2Earnings + $level3Earnings,
        ];
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
     * Check if user has reached their daily ad interaction limit
     */
    public function hasReachedDailyAdInteractionLimit(): bool
    {
        // If user doesn't have an active package, they can interact with ads
        if (!$this->hasActivePackage()) {
            return false;
        }
        
        // In the new system, users can only interact with one ad per day
        $adInteractionsToday = $this->adInteractions()
            ->whereDate('interacted_at', now()->toDateString())
            ->count();
            
        return $adInteractionsToday >= 1;
    }
    
    /**
     * Get the number of ad interactions remaining for today
     */
    public function getRemainingDailyAdInteractions(): int
    {
        // If user doesn't have an active package, they have no interactions
        if (!$this->hasActivePackage()) {
            return 0;
        }
        
        // In the new system, users can only interact with one ad per day
        $adInteractionsToday = $this->adInteractions()
            ->whereDate('interacted_at', now()->toDateString())
            ->count();
            
        return max(0, 1 - $adInteractionsToday);
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
     * Check if the user is an administrator
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }
    
    /**
     * Get available ads for the user based on their package limit
     */
    public function getAvailableAdsQuery()
    {
        // Start with active ads (this now also checks budget)
        $query = Advertisement::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            // Also check that ad spend hasn't reached budget
            ->where(function($q) {
                $q->where('budget', 0)
                  ->orWhereRaw('spent < budget');
            });
            
        // Exclude ads that the user has already interacted with today
        $interactedAdIds = $this->adInteractions()
            ->whereDate('interacted_at', today())
            ->pluck('advertisement_id');
            
        if ($interactedAdIds->isNotEmpty()) {
            $query->whereNotIn('id', $interactedAdIds);
        }
        
        // Limit to only 1 ad per day
        $query->limit(1);
        
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

    /**
     * Get level 1 referrals (direct referrals)
     */
    public function referralsLevel1(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Get level 2 referrals (referrals of referrals)
     */
    public function referralsLevel2(): HasMany
    {
        // Get IDs of level 1 referrals
        $level1Ids = $this->referrals()->pluck('id');
        
        return $this->hasMany(User::class, 'referred_by')->whereIn('referred_by', $level1Ids);
    }

    /**
     * Get level 3 referrals (referrals of referrals of referrals)
     */
    public function referralsLevel3(): HasMany
    {
        // Get IDs of level 1 referrals
        $level1Ids = $this->referrals()->pluck('id');
        
        // Get IDs of level 2 referrals
        $level2Ids = User::whereIn('referred_by', $level1Ids)->pluck('id');
        
        return $this->hasMany(User::class, 'referred_by')->whereIn('referred_by', $level2Ids);
    }

    /**
     * Get sponsored posts created by this user
     */
    public function sponsoredPosts(): HasMany
    {
        return $this->hasMany(SponsoredPost::class);
    }

    /**
     * Check if user has bound their bank account
     */
    public function hasBoundBankAccount(): bool
    {
        return !is_null($this->bank_account_bound_at);
    }

    /**
     * Bind bank account details to the user
     * This can only be done once
     */
    public function bindBankAccount(array $bankDetails): bool
    {
        // Check if bank account is already bound
        if ($this->hasBoundBankAccount()) {
            return false;
        }

        // Validate required bank account details
        $requiredFields = ['bank_account_holder_name', 'bank_account_number', 'bank_name'];
        foreach ($requiredFields as $field) {
            if (empty($bankDetails[$field])) {
                return false;
            }
        }

        // Encrypt sensitive bank account details before saving
        $this->forceFill([
            'bank_account_holder_name' => Crypt::encryptString($bankDetails['bank_account_holder_name']),
            'bank_account_number' => Crypt::encryptString($bankDetails['bank_account_number']),
            'bank_name' => Crypt::encryptString($bankDetails['bank_name']),
            'bank_branch' => !empty($bankDetails['bank_branch']) ? Crypt::encryptString($bankDetails['bank_branch']) : null,
            'bank_routing_number' => !empty($bankDetails['bank_routing_number']) ? Crypt::encryptString($bankDetails['bank_routing_number']) : null,
            'bank_account_bound_at' => now()
        ])->save();

        return true;
    }

    /**
     * Override the fill method to prevent updating bank account details once bound
     */
    public function fill(array $attributes)
    {
        // If bank account is already bound, remove bank account fields from attributes
        if ($this->hasBoundBankAccount()) {
            unset(
                $attributes['bank_account_holder_name'],
                $attributes['bank_account_number'],
                $attributes['bank_name'],
                $attributes['bank_branch'],
                $attributes['bank_routing_number']
            );
        }

        return parent::fill($attributes);
    }

    /**
     * Override the update method to prevent updating bank account details once bound
     */
    public function update(array $attributes = [], array $options = [])
    {
        // If bank account is already bound, remove bank account fields from attributes
        if ($this->hasBoundBankAccount()) {
            unset(
                $attributes['bank_account_holder_name'],
                $attributes['bank_account_number'],
                $attributes['bank_name'],
                $attributes['bank_branch'],
                $attributes['bank_routing_number']
            );
        }

        return parent::update($attributes, $options);
    }

    /**
     * Check if the user is a vendor
     */
    public function isVendor(): bool
    {
        return $this->is_vendor === true;
    }

    /**
     * Make the user a vendor
     */
    public function makeVendor(array $vendorData = []): void
    {
        $this->update(array_merge([
            'is_vendor' => true,
        ], $vendorData));
    }

    /**
     * Get vendor access keys
     */
    public function vendorAccessKeys(): HasMany
    {
        return $this->hasMany(VendorAccessKey::class, 'vendor_id');
    }

    /**
     * Get access keys created by this vendor
     */
    public function createdVendorAccessKeys(): HasMany
    {
        return $this->hasMany(VendorAccessKey::class, 'vendor_id');
    }

    /**
     * Get access keys sold by this vendor
     */
    public function soldVendorAccessKeys(): HasMany
    {
        return $this->hasMany(VendorAccessKey::class, 'vendor_id')->where('is_sold', true);
    }

    /**
     * Get total earnings from selling access keys
     */
    public function getTotalVendorEarnings(): float
    {
        return (float) $this->createdVendorAccessKeys()
            ->where('is_sold', true)
            ->sum('earned_amount');
    }

    /**
     * Get total access keys created by this vendor
     */
    public function getTotalVendorAccessKeys(): int
    {
        return $this->createdVendorAccessKeys()->count();
    }

    /**
     * Get total access keys sold by this vendor
     */
    public function getTotalSoldVendorAccessKeys(): int
    {
        return $this->soldVendorAccessKeys()->count();
    }
}