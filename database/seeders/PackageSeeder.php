<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds for packages.
     */
    public function run(): void
    {
        // Create sample packages
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

        foreach ($packages as $packageData) {
            Package::firstOrCreate(
                ['name' => $packageData['name']],
                $packageData
            );
        }

        $this->command->info('Packages seeded successfully!');
    }
}