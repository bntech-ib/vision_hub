<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'features' => 'array',
        'marketplace_access' => 'boolean',
        'brain_teaser_access' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get users who have this package
     */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'current_package_id');
    }
}