<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Tag;
use App\Models\UserPackage;
use App\Models\AccessKey; // Added
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the UserSeeder to create admin and demo users
        $this->call(UserSeeder::class);

        // Get the admin user for use in other seeders
        $adminUser = User::where('email', 'admin@visionhub.com')->first();
        $demoUser = User::where('email', 'demo@visionhub.com')->first();

        // Create sample packages
        if (class_exists('App\\Models\\UserPackage')) {
            $packages = [
                [
                    'name' => 'Basic Plan',
                    'description' => 'Perfect for individuals getting started',
                    'price' => 9.99,
                    'duration_days' => 30,
                    'features' => ['Up to 10 projects', 'Basic analytics', 'Email support'],
                    'ad_views_limit' => 100,
                    'marketplace_access' => false,
                    'brain_teaser_access' => true,
                    'is_active' => true,
                ],
                [
                    'name' => 'Professional Plan',
                    'description' => 'Ideal for professionals and small teams',
                    'price' => 29.99,
                    'duration_days' => 30,
                    'features' => ['Unlimited projects', 'Advanced analytics', 'Priority support', 'Custom branding'],
                    'ad_views_limit' => 1000,
                    'marketplace_access' => true,
                    'brain_teaser_access' => true,
                    'is_active' => true,
                ],
                [
                    'name' => 'Enterprise Plan',
                    'description' => 'For large organizations with advanced needs',
                    'price' => 99.99,
                    'duration_days' => 30,
                    'features' => ['Unlimited projects', 'Advanced analytics', '24/7 dedicated support', 'Custom branding', 'API access'],
                    'ad_views_limit' => 10000,
                    'marketplace_access' => true,
                    'brain_teaser_access' => true,
                    'is_active' => true,
                ],
            ];

            $createdPackages = [];
            foreach ($packages as $packageData) {
                $createdPackages[] = UserPackage::firstOrCreate(
                    ['name' => $packageData['name']],
                    $packageData
                );
            }
        }

        // Create sample access keys
        if (isset($createdPackages) && !empty($createdPackages)) {
            foreach ($createdPackages as $package) {
                // Check if access keys already exist for this package
                $existingKeys = AccessKey::where('package_id', $package->id)->count();
                
                // Create 3 access keys for each package if they don't exist
                if ($existingKeys == 0) {
                    for ($i = 0; $i < 3; $i++) {
                        AccessKey::create([
                            'key' => strtoupper(Str::random(16)),
                            'package_id' => $package->id,
                            'created_by' => $adminUser->id,
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }

        // Create sample tags if Tag model exists
        if (class_exists('App\\Models\\Tag')) {
            $tags = [
                ['name' => 'Portrait', 'color' => '#FF6B6B', 'description' => 'Portrait photography'],
                ['name' => 'Landscape', 'color' => '#4ECDC4', 'description' => 'Landscape and nature photography'],
                ['name' => 'Architecture', 'color' => '#45B7D1', 'description' => 'Architectural photography'],
                ['name' => 'Street', 'color' => '#96CEB4', 'description' => 'Street photography'],
                ['name' => 'Abstract', 'color' => '#FFEAA7', 'description' => 'Abstract and artistic images'],
                ['name' => 'Wildlife', 'color' => '#DDA0DD', 'description' => 'Wildlife and animal photography'],
                ['name' => 'Macro', 'color' => '#98D8C8', 'description' => 'Macro and close-up photography'],
                ['name' => 'Black & White', 'color' => '#6C5CE7', 'description' => 'Monochrome photography'],
            ];

            foreach ($tags as $tagData) {
                Tag::firstOrCreate(
                    ['name' => $tagData['name']],
                    array_merge($tagData, ['created_by' => $adminUser->id])
                );
            }
        }

        // Create sample projects if Project model exists
        if (class_exists('App\\Models\\Project')) {
            $projects = [
                [
                    'name' => 'Wedding Photography Collection',
                    'description' => 'A comprehensive collection of wedding photographs from various events',
                    'status' => 'active',
                    'settings' => ['auto_tag' => true, 'quality' => 'high'],
                    'user_id' => $adminUser->id,
                ],
                [
                    'name' => 'Nature & Wildlife Portfolio',
                    'description' => 'Stunning nature and wildlife photographs from around the world',
                    'status' => 'active',
                    'settings' => ['watermark' => true, 'backup' => true],
                    'user_id' => $adminUser->id,
                ],
                [
                    'name' => 'Urban Architecture Study',
                    'description' => 'Modern architecture and urban landscapes documentation',
                    'status' => 'completed',
                    'completed_at' => now()->subDays(10),
                    'user_id' => $demoUser->id,
                ],
                [
                    'name' => 'Product Photography',
                    'description' => 'Commercial product photography for e-commerce',
                    'status' => 'active',
                    'user_id' => $demoUser->id,
                ],
            ];

            foreach ($projects as $projectData) {
                Project::firstOrCreate(
                    ['name' => $projectData['name'], 'user_id' => $projectData['user_id']],
                    $projectData
                );
            }
        }

        // Create additional demo users (only if there are less than 10 users)
        $userCount = User::count();
        if ($userCount < 10) {
            User::factory(10 - $userCount)->create();
        }

        // Run the new seeders
        $this->call([
            WithdrawalRequestSeeder::class,
            TransactionSeeder::class,
            CourseSeeder::class,
            SponsoredPostSeeder::class,
            BrainTeaserSeeder::class,
            ProductSeeder::class,
            AdvertisementSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Demo credentials:');
        $this->command->info('Admin: admin@visionhub.com / admin123');
        $this->command->info('Demo: demo@visionhub.com / password123');
    }
}