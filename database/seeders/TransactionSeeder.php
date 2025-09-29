<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Product;
use App\Models\Course;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users for transactions
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Skipping transaction seeding.');
            return;
        }
        
        $types = ['earning', 'purchase', 'withdrawal', 'refund'];
        $statuses = ['pending', 'completed', 'failed'];
        $referenceTypes = [
            'App\Models\Product',
            'App\Models\Course',
            'App\Models\Advertisement'
        ];
        
        // Create 10-20 transactions
        for ($i = 0; $i < rand(10, 20); $i++) {
            $user = $users->random();
            $type = $types[array_rand($types)];
            $status = $statuses[array_rand($statuses)];
            $referenceType = $referenceTypes[array_rand($referenceTypes)];
            
            // Get a random reference model
            $reference = null;
            switch ($referenceType) {
                case 'App\Models\Product':
                    $reference = Product::inRandomOrder()->first();
                    break;
                case 'App\Models\Course':
                    $reference = Course::inRandomOrder()->first();
                    break;
                case 'App\Models\Advertisement':
                    $reference = \App\Models\Advertisement::inRandomOrder()->first();
                    break;
            }
            
            if (!$reference) {
                continue;
            }
            
            Transaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => rand(10, 500) + (rand(0, 99) / 100),
                'description' => $this->getDescription($type, $referenceType),
                'metadata' => $this->getMetadata($referenceType, $reference),
                'status' => $status,
                'reference_type' => $referenceType,
                'reference_id' => $reference->id
            ]);
        }
        
        $this->command->info('Transactions seeded successfully!');
    }
    
    private function getDescription(string $type, string $referenceType): string
    {
        $descriptions = [
            'earning' => [
                'App\Models\Product' => 'Product sale commission',
                'App\Models\Course' => 'Course enrollment commission',
                'App\Models\Advertisement' => 'Advertisement view earnings'
            ],
            'purchase' => [
                'App\Models\Product' => 'Product purchase',
                'App\Models\Course' => 'Course enrollment',
                'App\Models\Advertisement' => 'Advertisement placement'
            ],
            'withdrawal' => [
                'App\Models\Product' => 'Withdrawal for product sales',
                'App\Models\Course' => 'Withdrawal for course sales',
                'App\Models\Advertisement' => 'Withdrawal for ad earnings'
            ],
            'refund' => [
                'App\Models\Product' => 'Product purchase refund',
                'App\Models\Course' => 'Course enrollment refund',
                'App\Models\Advertisement' => 'Advertisement refund'
            ]
        ];
        
        return $descriptions[$type][$referenceType] ?? 'Transaction';
    }
    
    private function getMetadata(string $referenceType, $reference): array
    {
        switch ($referenceType) {
            case 'App\Models\Product':
                return [
                    'product_name' => $reference->name,
                    'product_id' => $reference->id,
                    'category' => $reference->category
                ];
            case 'App\Models\Course':
                return [
                    'course_title' => $reference->title,
                    'course_id' => $reference->id,
                    'instructor_id' => $reference->instructor_id
                ];
            case 'App\Models\Advertisement':
                return [
                    'ad_title' => $reference->title,
                    'ad_id' => $reference->id,
                    'ad_type' => $reference->type
                ];
            default:
                return [];
        }
    }
}