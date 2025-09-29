<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description', 
        'user_id',
        'status',
        'settings',
        'completed_at'
    ];

    protected $casts = [
        'settings' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the project
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the images for the project
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    /**
     * Get the processing jobs through images
     */
    public function processingJobs(): HasManyThrough
    {
        return $this->hasManyThrough(ProcessingJob::class, Image::class);
    }

    /**
     * Scope active projects
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope completed projects
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}