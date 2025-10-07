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
            // Update the status ENUM to include 'refunded' and 'partial_refund' for consistency
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded', 'partial_refund'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Revert to the original ENUM values
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending')->change();
        });
    }
};