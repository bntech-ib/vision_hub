<?php

namespace Database\Seeders;

use App\Models\AccessKey;
use App\Models\User;
use App\Models\UserPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccessKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $adminUser = User::where('email', 'admin@visionhub.com')->first();
        
        if (!$adminUser) {
            $this->command->error('Admin user not found. Please run DatabaseSeeder first.');
            return;
        }

        // Get all packages
        $packages = UserPackage::all();
        
        if ($packages->isEmpty()) {
            $this->command->error('No packages found. Please run DatabaseSeeder first.');
            return;
        }

        // Create sample access keys
        foreach ($packages as $package) {
            // Create 3 access keys for each package
            for ($i = 0; $i < 3; $i++) {
                AccessKey::create([
                    'key' => strtoupper(Str::random(16)),
                    'package_id' => $package->id,
                    'created_by' => $adminUser->id,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Access keys seeded successfully!');
    }
}