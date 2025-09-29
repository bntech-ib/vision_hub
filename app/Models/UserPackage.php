<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPackage extends Model
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
        'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'marketplace_access' => 'boolean',
        'brain_teaser_access' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2'
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
}