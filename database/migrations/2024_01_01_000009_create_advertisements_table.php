<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('image_url')->nullable();
            $table->string('click_url')->nullable();
            $table->enum('type', ['banner', 'video', 'interactive']);
            $table->decimal('reward_amount', 8, 2); // Amount user earns per view
            $table->integer('max_views')->default(0); // 0 = unlimited
            $table->integer('current_views')->default(0);
            $table->json('targeting')->nullable(); // Targeting criteria
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->enum('status', ['active', 'paused', 'completed', 'expired'])->default('active');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['status', 'start_date']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};