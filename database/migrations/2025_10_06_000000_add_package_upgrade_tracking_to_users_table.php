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
        Schema::table('users', function (Blueprint $table) {
            // Add a field to track the last package upgrade timestamp
            $table->timestamp('last_package_upgrade_at')->nullable()->after('package_expires_at');
            
            // Add a field to track the previous package ID for audit purposes
            $table->unsignedBigInteger('previous_package_id')->nullable()->after('current_package_id');
            
            // Add foreign key constraint for previous_package_id
            $table->foreign('previous_package_id')
                  ->references('id')
                  ->on('user_packages')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['previous_package_id']);
            $table->dropColumn(['last_package_upgrade_at', 'previous_package_id']);
        });
    }
};