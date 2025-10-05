<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorAccessKey extends Model
{
    protected $fillable = [
        'vendor_id',
        'access_key_id',
        'commission_rate',
        'is_sold',
        'sold_to_user_id',
        'sold_at',
        'earned_amount'
    ];

    protected $casts = [
        'is_sold' => 'boolean',
        'commission_rate' => 'decimal:2',
        'earned_amount' => 'decimal:2',
        'sold_at' => 'datetime'
    ];

    /**
     * Get the vendor (user) that owns this vendor access key
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the access key
     */
    public function accessKey(): BelongsTo
    {
        return $this->belongsTo(AccessKey::class);
    }

    /**
     * Get the user who bought this access key
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_to_user_id');
    }
}