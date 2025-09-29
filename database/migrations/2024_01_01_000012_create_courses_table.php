<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('thumbnail_url')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('category');
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced']);
            $table->integer('duration_hours');
            $table->json('curriculum')->nullable(); // Course modules/lessons
            $table->foreignId('instructor_id')->constrained('users');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_enrollments')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            
            $table->index(['category', 'status']);
            $table->index(['instructor_id', 'status']);
            $table->index(['difficulty', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};