<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create pivot table for course_images
        Schema::create('course_image', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['course_id', 'image_id']);
        });

        // Create pivot table for advertisement_images
        Schema::create('advertisement_image', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained()->onDelete('cascade');
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['advertisement_id', 'image_id']);
        });

        // Create pivot table for product_images
        Schema::create('product_image', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['product_id', 'image_id']);
        });

        // Create pivot table for sponsored_post_images
        Schema::create('sponsored_post_image', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsored_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['sponsored_post_id', 'image_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_image');
        Schema::dropIfExists('advertisement_image');
        Schema::dropIfExists('product_image');
        Schema::dropIfExists('sponsored_post_image');
    }
};