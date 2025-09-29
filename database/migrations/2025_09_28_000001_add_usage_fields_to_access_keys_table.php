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
            $table->integer('usage_limit')->nullable()->after('expires_at');
            $table->integer('usage_count')->default(0)->after('usage_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_keys', function (Blueprint $table) {
            $table->dropColumn(['usage_limit', 'usage_count']);
        });
    }
};