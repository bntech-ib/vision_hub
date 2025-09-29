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
        Schema::table('brain_teaser_attempts', function (Blueprint $table) {
            // Remove old columns
            $table->dropColumn(['selected_answer', 'points_earned', 'time_taken_seconds']);
            
            // Add new columns
            $table->string('answer')->after('brain_teaser_id');
            $table->timestamp('attempted_at')->nullable()->after('is_correct');
            $table->decimal('reward_earned', 8, 2)->default(0)->after('attempted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brain_teaser_attempts', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn(['answer', 'attempted_at', 'reward_earned']);
            
            // Add back old columns
            $table->integer('selected_answer')->after('brain_teaser_id');
            $table->integer('points_earned')->default(0)->after('is_correct');
            $table->integer('time_taken_seconds')->after('points_earned');
        });
    }
};