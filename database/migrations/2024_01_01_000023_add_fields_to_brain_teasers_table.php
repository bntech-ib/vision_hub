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
        Schema::table('brain_teasers', function (Blueprint $table) {
            $table->datetime('start_date')->nullable()->after('reward_amount');
            $table->datetime('end_date')->nullable()->after('start_date');
            $table->boolean('is_daily')->default(false)->after('end_date');
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft')->after('is_daily');
            $table->integer('total_attempts')->default(0)->after('status');
            $table->integer('correct_attempts')->default(0)->after('total_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brain_teasers', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'is_daily', 'status', 'total_attempts', 'correct_attempts']);
        });
    }
};