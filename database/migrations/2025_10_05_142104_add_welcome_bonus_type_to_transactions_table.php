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
        Schema::table('transactions', function (Blueprint $table) {
            // Update the enum values to include all existing types plus 'welcome_bonus'
            $table->enum('type', ['earning', 'purchase', 'withdrawal', 'refund', 'referral_earning', 'welcome_bonus'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Revert to original enum values (excluding 'referral_earning' and 'welcome_bonus')
            $table->enum('type', ['earning', 'purchase', 'withdrawal', 'refund'])->change();
        });
    }
};