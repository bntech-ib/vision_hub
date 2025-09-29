<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get seller users
        $sellers = User::where('is_admin', false)->take(5)->get();
        
        if ($sellers->isEmpty()) {
            $this->command->info('No sellers found. Skipping product seeding.');
            return;
        }
        
        $categories = ['Photography', 'Design', 'Templates', 'Courses', 'Software', 'Hardware'];
        $statuses = ['active', 'inactive', 'out_of_stock'];
        
        $products = [
            [
                'name' => 'Professional Camera',
                'description' => 'High-quality DSLR camera perfect for professional photographers.',
                'category' => 'Hardware',
                'price' => 899.99
            ],
            [
                'name' => 'Photo Editing Software',
                'description' => 'Professional photo editing software with advanced features.',
                'category' => 'Software',
                'price' => 199.99
            ],
            [
                'name' => 'Wedding Photography Template',
                'description' => 'Beautiful wedding photography website template with gallery features.',
                'category' => 'Templates',
                'price' => 49.99
            ],
            [
                'name' => 'Portrait Lighting Kit',
                'description' => 'Professional lighting kit for portrait photography.',
                'category' => 'Hardware',
                'price' => 299.99
            ],
            [
                'name' => 'Graphic Design Course',
                'description' => 'Comprehensive course on graphic design principles and techniques.',
                'category' => 'Courses',
                'price' => 89.99
            ],
            [
                'name' => 'Photo Presets Pack',
                'description' => 'Collection of 50 professional photo presets for Lightroom.',
                'category' => 'Design',
                'price' => 29.99
            ],
            [
                'name' => 'Drone for Photography',
                'description' => 'High-quality drone with 4K camera for aerial photography.',
                'category' => 'Hardware',
                'price' => 599.99
            ],
            [
                'name' => 'Mobile Photography Course',
                'description' => 'Learn how to take professional photos with your smartphone.',
                'category' => 'Courses',
                'price' => 59.99
            ],
            [
                'name' => 'Studio Backgrounds Set',
                'description' => 'Set of 10 professional studio backgrounds for photography.',
                'category' => 'Photography',
                'price' => 149.99
            ],
            [
                'name' => 'Logo Design Template',
                'description' => 'Professional logo design templates for various industries.',
                'category' => 'Design',
                'price' => 39.99
            ]
        ];
        
        foreach ($products as $productData) {
            $seller = $sellers->random();
            $status = $statuses[array_rand($statuses)];
            
            Product::create([
                'seller_id' => $seller->id,
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'category' => $productData['category'],
                'images' => null,
                'stock_quantity' => $status !== 'out_of_stock' ? rand(1, 100) : 0,
                'specifications' => $this->generateSpecifications($productData['category']),
                'status' => $status,
                'rating' => rand(30, 50) / 10,
                'total_reviews' => rand(0, 50),
                'is_featured' => rand(0, 1) === 1,
                'view_count' => rand(0, 500)
            ]);
        }
        
        $this->command->info('Products seeded successfully!');
    }
    
    private function generateSpecifications(string $category): array
    {
        $specs = [
            'Hardware' => [
                'brand' => ['Canon', 'Nikon', 'Sony', 'Fujifilm'][array_rand(['Canon', 'Nikon', 'Sony', 'Fujifilm'])],
                'warranty' => rand(1, 3) . ' years',
                'weight' => rand(500, 2000) . 'g'
            ],
            'Software' => [
                'platform' => ['Windows', 'Mac', 'Linux'][array_rand(['Windows', 'Mac', 'Linux'])],
                'version' => 'v' . rand(1, 5) . '.' . rand(0, 9),
                'license' => ['Single User', 'Multi User', 'Enterprise'][array_rand(['Single User', 'Multi User', 'Enterprise'])]
            ],
            'Templates' => [
                'format' => ['PSD', 'AI', 'Figma', 'Sketch'][array_rand(['PSD', 'AI', 'Figma', 'Sketch'])],
                'pages' => rand(1, 20) . ' pages',
                'responsive' => rand(0, 1) === 1 ? 'Yes' : 'No'
            ],
            'Courses' => [
                'duration' => rand(2, 20) . ' hours',
                'level' => ['Beginner', 'Intermediate', 'Advanced'][array_rand(['Beginner', 'Intermediate', 'Advanced'])],
                'certificate' => rand(0, 1) === 1 ? 'Yes' : 'No'
            ],
            'Photography' => [
                'material' => ['Canvas', 'Paper', 'Metal'][array_rand(['Canvas', 'Paper', 'Metal'])],
                'size' => rand(8, 24) . 'x' . rand(8, 24) . ' inches',
                'finish' => ['Matte', 'Glossy', 'Satin'][array_rand(['Matte', 'Glossy', 'Satin'])]
            ],
            'Design' => [
                'file_type' => ['PSD', 'AI', 'PDF', 'PNG'][array_rand(['PSD', 'AI', 'PDF', 'PNG'])],
                'layers' => rand(0, 1) === 1 ? 'Editable' : 'Flattened',
                'resolution' => '300 DPI'
            ]
        ];
        
        return $specs[$category] ?? [];
    }
}