<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brain_teasers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('question');
            $table->json('options'); // Multiple choice options
            $table->string('correct_answer');
            $table->text('explanation')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->string('category');
            $table->decimal('reward_amount', 8, 2); // Reward for correct answer
            $table->integer('time_limit_seconds')->default(60);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['difficulty', 'is_active']);
            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brain_teasers');
    }
};