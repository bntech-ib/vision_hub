<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->json('features'); // Package features as JSON
            $table->integer('ad_views_limit')->nullable();
            $table->integer('course_access_limit')->nullable();
            $table->boolean('marketplace_access')->default(false);
            $table->boolean('brain_teaser_access')->default(false);
            $table->integer('duration_days'); // Package duration in days
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_packages');
    }
};