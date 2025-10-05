<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if columns already exist before adding them
        if (!Schema::hasColumn('packages', 'daily_earning_limit')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->decimal('daily_earning_limit', 10, 2)->default(0)->after('ad_views_limit');
            });
        }
        
        if (!Schema::hasColumn('packages', 'ad_limits')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->integer('ad_limits')->default(0)->after('daily_earning_limit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'daily_earning_limit')) {
                $table->dropColumn('daily_earning_limit');
            }
            if (Schema::hasColumn('packages', 'ad_limits')) {
                $table->dropColumn('ad_limits');
            }
        });
    }
};