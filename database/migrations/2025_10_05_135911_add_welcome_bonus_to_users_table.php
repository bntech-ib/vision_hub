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
        // Check if column already exists before adding it
        if (!Schema::hasColumn('users', 'welcome_bonus')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('welcome_bonus', 10, 2)->default(0)->after('wallet_balance');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'welcome_bonus')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('welcome_bonus');
            });
        }
    }
};