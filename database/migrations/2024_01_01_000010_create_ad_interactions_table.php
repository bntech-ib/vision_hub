<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('advertisement_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['view', 'click', 'completion']);
            $table->decimal('reward_earned', 8, 2)->default(0);
            $table->json('metadata')->nullable(); // Additional interaction data
            $table->timestamp('interacted_at');
            $table->timestamps();
            
            $table->index(['user_id', 'advertisement_id']);
            $table->index(['type', 'interacted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_interactions');
    }
};