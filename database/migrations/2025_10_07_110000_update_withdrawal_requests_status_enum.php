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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Update the status ENUM to include 'approved' for consistency
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected', 'approved'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Revert to the original ENUM values
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending')->change();
        });
    }
};