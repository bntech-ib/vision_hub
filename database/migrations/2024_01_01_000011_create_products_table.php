<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('category');
            $table->json('images')->nullable(); // Array of image URLs
            $table->integer('stock_quantity')->default(0);
            $table->json('specifications')->nullable(); // Product specs as JSON
            $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active');
            $table->foreignId('seller_id')->constrained('users');
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            
            $table->index(['category', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['is_featured', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};