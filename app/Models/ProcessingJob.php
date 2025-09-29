<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

// Import related models
use App\Models\User;
use App\Models\Image;

class ProcessingJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'image_id',
        'user_id',
        'job_type',
        'parameters',
        'status',
        'result',
        'error_message',
        'started_at',
        'completed_at',
        'progress'
    ];

    protected $casts = [
        'parameters' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($job) {
            if (empty($job->job_id)) {
                $job->job_id = Str::uuid();
            }
        });
    }

    /**
     * Get the image that owns the processing job
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * Get the user who created the processing job
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark job as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark job as completed
     */
    public function markAsCompleted(array $result = []): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress' => 100,
            'result' => $result,
        ]);
    }

    /**
     * Mark job as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Update job progress
     */
    public function updateProgress(int $progress): void
    {
        $this->update(['progress' => $progress]);
    }

    /**
     * Scope pending jobs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope processing jobs
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope completed jobs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}