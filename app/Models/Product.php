<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'images',
        'stock_quantity',
        'specifications',
        'status',
        'seller_id',
        'rating',
        'total_reviews',
        'is_featured',
        'view_count'
    ];

    protected $casts = [
        'images' => 'array',
        'specifications' => 'array',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_featured' => 'boolean',
        'view_count' => 'integer'
    ];

    /**
     * Get the seller
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get product images
     */
    public function imageRecords(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'product_image');
    }

    /**
     * Check if product is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->stock_quantity > 0;
    }

    /**
     * Decrease stock quantity
     */
    public function decreaseStock(int $quantity = 1): void
    {
        $this->decrement('stock_quantity', $quantity);
        
        if ($this->stock_quantity <= 0) {
            $this->update(['status' => 'out_of_stock']);
        }
    }
}