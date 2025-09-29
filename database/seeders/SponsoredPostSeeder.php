<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SponsoredPost;
use App\Models\User;

class SponsoredPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users for sponsored posts
        $users = User::where('is_admin', false)->take(5)->get();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Skipping sponsored post seeding.');
            return;
        }
        
        $categories = ['Technology', 'Fashion', 'Food', 'Travel', 'Health', 'Education', 'Entertainment'];
        $statuses = ['draft', 'active', 'paused', 'completed'];
        
        $posts = [
            [
                'title' => 'New Smartphone Launch',
                'description' => 'Check out the latest smartphone with revolutionary camera technology.',
                'image_url' => null,
                'target_url' => 'https://example.com/smartphone',
                'category' => 'Technology'
            ],
            [
                'title' => 'Summer Fashion Collection',
                'description' => 'Discover our new summer collection with exclusive discounts.',
                'image_url' => null,
                'target_url' => 'https://example.com/fashion',
                'category' => 'Fashion'
            ],
            [
                'title' => 'Gourmet Cooking Class',
                'description' => 'Learn to cook gourmet meals with our professional chefs.',
                'image_url' => null,
                'target_url' => 'https://example.com/cooking',
                'category' => 'Food'
            ],
            [
                'title' => 'Adventure Travel Packages',
                'description' => 'Experience the thrill of adventure with our exclusive travel packages.',
                'image_url' => null,
                'target_url' => 'https://example.com/travel',
                'category' => 'Travel'
            ],
            [
                'title' => 'Fitness Program Launch',
                'description' => 'Transform your body with our 30-day fitness challenge.',
                'image_url' => null,
                'target_url' => 'https://example.com/fitness',
                'category' => 'Health'
            ],
            [
                'title' => 'Online Learning Platform',
                'description' => 'Access thousands of courses from top universities and institutions.',
                'image_url' => null,
                'target_url' => 'https://example.com/learning',
                'category' => 'Education'
            ],
            [
                'title' => 'Movie Premiere Tickets',
                'description' => 'Be the first to watch the latest blockbuster movie.',
                'image_url' => null,
                'target_url' => 'https://example.com/movies',
                'category' => 'Entertainment'
            ]
        ];
        
        foreach ($posts as $postData) {
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            
            SponsoredPost::create([
                'user_id' => $user->id,
                'title' => $postData['title'],
                'description' => $postData['description'],
                'image_url' => $postData['image_url'],
                'target_url' => $postData['target_url'],
                'category' => $postData['category'],
                'budget' => rand(100, 1000) + (rand(0, 99) / 100),
                'status' => $status,
                'start_date' => $status !== 'draft' ? now()->subDays(rand(1, 30)) : null,
                'end_date' => $status === 'active' ? now()->addDays(rand(1, 60)) : null
            ]);
        }
        
        $this->command->info('Sponsored posts seeded successfully!');
    }
}