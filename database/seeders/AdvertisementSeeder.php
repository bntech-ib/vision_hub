<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Advertisement;
use App\Models\User;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users for advertisement creators
        $users = User::where('is_admin', false)->take(5)->get();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Skipping advertisement seeding.');
            return;
        }
        
        $types = ['banner', 'video', 'interactive'];
        $categories = ['Technology', 'Fashion', 'Food', 'Travel', 'Health', 'Education', 'Entertainment'];
        $statuses = ['active', 'paused', 'completed', 'expired', 'pending', 'rejected'];
        
        $ads = [
            [
                'title' => 'Summer Sale - Up to 50% Off',
                'description' => 'Limited time offer on selected items. Don\'t miss out on these amazing deals.',
                'type' => 'banner'
            ],
            [
                'title' => 'New Product Launch',
                'description' => 'Introducing our latest innovation. Experience the future today.',
                'type' => 'video'
            ],
            [
                'title' => 'Free Trial - 30 Days',
                'description' => 'Try our premium service free for 30 days. No credit card required.',
                'type' => 'interactive'
            ],
            [
                'title' => 'Exclusive Workshop',
                'description' => 'Join our expert-led workshop and enhance your skills.',
                'type' => 'banner'
            ],
            [
                'title' => 'Customer Success Story',
                'description' => 'See how our product transformed this business.',
                'type' => 'video'
            ],
            [
                'title' => 'Quiz - Find Your Perfect Solution',
                'description' => 'Take our interactive quiz to discover the best product for your needs.',
                'type' => 'interactive'
            ],
            [
                'title' => 'Limited Edition Release',
                'description' => 'Special edition product available for a short time only.',
                'type' => 'banner'
            ],
            [
                'title' => 'How It Works',
                'description' => 'Learn how our solution can benefit you in just 60 seconds.',
                'type' => 'video'
            ]
        ];
        
        foreach ($ads as $adData) {
            $creator = $users->random();
            $status = $statuses[array_rand($statuses)];
            $type = $adData['type'];
            $category = $categories[array_rand($categories)];
            
            Advertisement::create([
                'advertiser_id' => $creator->id,
                'title' => $adData['title'],
                'description' => $adData['description'],
                'image_url' => null,
                'target_url' => 'https://example.com/ad-' . rand(1, 100),
                'category' => $category,
                'type' => $type,
                'reward_amount' => rand(0, 5) + (rand(0, 99) / 100),
                'max_views' => rand(0, 1) === 1 ? rand(1000, 10000) : 0, // 0 = unlimited
                'current_views' => rand(0, 5000),
                'targeting' => $this->generateTargeting(),
                'start_date' => $status !== 'pending' ? now()->subDays(rand(1, 30)) : now(),
                'end_date' => in_array($status, ['active', 'paused']) ? now()->addDays(rand(1, 60)) : null,
                'status' => $status
            ]);
        }
        
        $this->command->info('Advertisements seeded successfully!');
    }
    
    private function generateTargeting(): array
    {
        $targeting = [];
        
        // Age targeting
        if (rand(0, 1) === 1) {
            $targeting['age'] = [
                'min' => rand(18, 25),
                'max' => rand(35, 65)
            ];
        }
        
        // Gender targeting
        if (rand(0, 1) === 1) {
            $targeting['gender'] = ['male', 'female', 'other'][array_rand(['male', 'female', 'other'])];
        }
        
        // Location targeting
        if (rand(0, 1) === 1) {
            $countries = ['US', 'UK', 'CA', 'AU', 'DE'];
            $selectedCountries = [];
            $numCountries = rand(1, 3);
            
            // Select random countries
            for ($i = 0; $i < $numCountries; $i++) {
                $selectedCountries[] = $countries[array_rand($countries)];
            }
            
            // Remove duplicates
            $selectedCountries = array_unique($selectedCountries);
            
            $targeting['location'] = [
                'countries' => $selectedCountries
            ];
        }
        
        // Interest targeting
        if (rand(0, 1) === 1) {
            $interests = ['photography', 'technology', 'travel', 'food', 'fashion', 'sports', 'music'];
            $selectedInterests = [];
            $numInterests = rand(1, 3);
            
            // Select random interests
            for ($i = 0; $i < $numInterests; $i++) {
                $selectedInterests[] = $interests[array_rand($interests)];
            }
            
            // Remove duplicates
            $selectedInterests = array_unique($selectedInterests);
            
            $targeting['interests'] = $selectedInterests;
        }
        
        return $targeting;
    }
}