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
        Schema::table('advertisements', function (Blueprint $table) {
            // Update the status column to include all values used in the application
            $table->enum('status', ['active', 'paused', 'completed', 'expired', 'pending', 'rejected'])
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            // Revert to the original status column definition
            $table->enum('status', ['active', 'paused', 'completed', 'expired'])
                  ->default('active')
                  ->change();
        });
    }
};