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
            // Update the enum values to include all existing types plus 'withdrawal_request'
            $table->enum('type', ['earning', 'purchase', 'withdrawal', 'refund', 'referral_earning', 'welcome_bonus', 'withdrawal_request'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Revert to original enum values (excluding 'withdrawal_request')
            $table->enum('type', ['earning', 'purchase', 'withdrawal', 'refund', 'referral_earning', 'welcome_bonus'])->change();
        });
    }
};