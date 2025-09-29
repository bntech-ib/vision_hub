<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SponsoredPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'image_url', 'target_url', 'category', 'budget', 'status', 'start_date', 'end_date', 'user_id'
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the user who created the sponsored post
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get sponsored post images
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'sponsored_post_image');
    }
}