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
        Schema::table('access_keys', function (Blueprint $table) {
            // Add a field to track upgrade request timestamp
            $table->timestamp('upgrade_requested_at')->nullable()->after('used_at');
            
            // Add a field to track IP address of upgrade request
            $table->string('upgrade_request_ip', 45)->nullable()->after('upgrade_requested_at');
            
            // Add a field to track user agent of upgrade request
            $table->text('upgrade_request_user_agent')->nullable()->after('upgrade_request_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_keys', function (Blueprint $table) {
            $table->dropColumn([
                'upgrade_requested_at',
                'upgrade_request_ip',
                'upgrade_request_user_agent'
            ]);
        });
    }
};