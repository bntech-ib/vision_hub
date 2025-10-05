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
        Schema::table('user_packages', function (Blueprint $table) {
            $table->decimal('referral_earning_percentage', 10, 2)->default(0)->change();
        });
        
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('referral_earning_percentage', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_packages', function (Blueprint $table) {
            $table->decimal('referral_earning_percentage', 5, 2)->default(0)->change();
        });
        
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('referral_earning_percentage', 5, 2)->default(0)->change();
        });
    }
};