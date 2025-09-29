<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'title',
        'description',
        'category',
        'difficulty',
        'price',
        'duration_hours',
        'thumbnail_url',
        'video_source',
        'curriculum',
        'tags',
        'status',
        'rating',
        'total_enrollments',
        'view_count'
    ];

    protected $casts = [
        'curriculum' => 'array',
        'tags' => 'array',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'total_enrollments' => 'integer',
        'view_count' => 'integer'
    ];

    /**
     * Get the instructor
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get course enrollments
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Get course images
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'course_image');
    }

    /**
     * Check if course is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}