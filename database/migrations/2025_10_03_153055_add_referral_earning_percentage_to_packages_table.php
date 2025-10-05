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
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('referral_earning_percentage', 5, 2)->default(0)->after('is_active');
        });
        
        Schema::table('user_packages', function (Blueprint $table) {
            $table->decimal('referral_earning_percentage', 5, 2)->default(0)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('referral_earning_percentage');
        });
        
        Schema::table('user_packages', function (Blueprint $table) {
            $table->dropColumn('referral_earning_percentage');
        });
    }
};