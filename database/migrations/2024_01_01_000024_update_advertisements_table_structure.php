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
            // Rename columns to match controller expectations
            $table->renameColumn('click_url', 'target_url');
            $table->renameColumn('created_by', 'advertiser_id');
            
            // Add missing columns
            $table->string('category')->after('target_url');
            $table->decimal('budget', 10, 2)->default(0)->after('category');
            $table->decimal('spent', 10, 2)->default(0)->after('budget');
            
            // Update status enum values to match controller
            // Note: MySQL doesn't support changing enum values directly, so we'll handle this in code
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->renameColumn('target_url', 'click_url');
            $table->renameColumn('advertiser_id', 'created_by');
            $table->dropColumn(['category', 'budget', 'spent']);
        });
    }
};