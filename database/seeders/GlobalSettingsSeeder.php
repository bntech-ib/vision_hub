<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GlobalSetting;

class GlobalSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default global settings
        GlobalSetting::updateOrCreate(
            ['key' => 'withdrawal_enabled'],
            [
                'value' => true,
                'description' => 'Globally enable or disable withdrawal functionality for all users'
            ]
        );
    }
}