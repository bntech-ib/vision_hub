<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert or update the default welcome bonus setting
        DB::table('global_settings')->updateOrInsert(
            ['key' => 'welcome_bonus_amount'],
            [
                'value' => '500.00',
                'description' => 'Default welcome bonus amount for new users',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the welcome bonus setting
        DB::table('global_settings')->where('key', 'welcome_bonus_amount')->delete();
    }
};