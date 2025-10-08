<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AccessKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'package_id',
        'created_by',
        'used_by',
        'expires_at',
        'is_active',
        'is_used',
        'usage_limit',
        'usage_count'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_used' => 'boolean',
        'usage_limit' => 'integer',
        'usage_count' => 'integer'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Generate key when creating
        static::creating(function ($accessKey) {
            if (empty($accessKey->key)) {
                $accessKey->key = $accessKey->generateKey();
            }
        });
    }

    /**
     * Generate access key with package-specific prefix
     */
    public function generateKey(): string
    {
        // Get the package to determine prefix
        $package = $this->package ?? UserPackage::find($this->package_id);
        
        // Default prefix if no package
        $prefix = 'VS1';
        
        if ($package) {
            // Generate prefix based on package ID
            $prefixes = ['VS1', 'VX2', 'VP3', 'VQ4', 'VR5', 'VT6', 'VY7', 'VU8', 'VI9', 'VO0'];
            $index = ($package->id - 1) % count($prefixes);
            $prefix = $prefixes[$index];
        }
        
        // Generate random parts
        $part1 = strtoupper(Str::random(4));
        $part2 = strtoupper(Str::random(4));
        $part3 = strtoupper(Str::random(4));
        $part4 = strtoupper(Str::random(4));
        
        // Format as: PREFIX-PART1-PART2-PART3-PART4
        return $prefix . '-' . $part1 . '-' . $part2 . '-' . $part3 . '-' . $part4;
    }

    /**
     * Get formatted key for display
     */
    public function getFormattedKeyAttribute(): string
    {
        return $this->key;
    }

    /**
     * Get the package associated with the access key
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(UserPackage::class, 'package_id');
    }

    /**
     * Get the user who created the access key
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who used the access key
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Check if the access key is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the access key can still be used
     */
    public function canBeUsed(): bool
    {
        return $this->is_active && 
               !$this->isExpired() && 
               !$this->is_used &&
               ($this->usage_limit === null || $this->usage_count < $this->usage_limit);
    }

    /**
     * Use the access key
     */
    public function useKey(User $user): bool
    {
        if (!$this->canBeUsed()) {
            return false;
        }

        $this->increment('usage_count');
        
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            $this->update(['is_active' => false]);
        }

        $this->update(['used_by' => $user->id]);

        return true;
    }
}