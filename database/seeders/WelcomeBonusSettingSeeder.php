<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WelcomeBonusSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert the default welcome bonus setting
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
}