<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brain_teaser_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('brain_teaser_id')->constrained()->onDelete('cascade');
            $table->integer('selected_answer');
            $table->boolean('is_correct');
            $table->integer('points_earned')->default(0);
            $table->integer('time_taken_seconds');
            $table->timestamps();
            
            $table->index(['user_id', 'brain_teaser_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brain_teaser_attempts');
    }
};