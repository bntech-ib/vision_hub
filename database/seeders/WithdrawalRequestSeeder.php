<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WithdrawalRequest;
use App\Models\User;

class WithdrawalRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users for withdrawal requests
        $users = User::where('is_admin', false)->take(5)->get();
        
        if ($users->isEmpty()) {
            $this->command->info('No regular users found. Skipping withdrawal request seeding.');
            return;
        }
        
        $paymentMethods = ['bank', 'paypal', 'stripe', 'crypto'];
        $statuses = ['pending', 'processing', 'completed', 'rejected'];
        
        foreach ($users as $user) {
            // Create 1-3 withdrawal requests per user
            $requestsCount = rand(1, 3);
            
            for ($i = 0; $i < $requestsCount; $i++) {
                $status = $statuses[array_rand($statuses)];
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                
                WithdrawalRequest::create([
                    'user_id' => $user->id,
                    'amount' => rand(10, 500) + (rand(0, 99) / 100), // Random amount between 10.00 and 500.99
                    'payment_method' => $paymentMethod,
                    'payment_details' => $this->getPaymentDetails($paymentMethod),
                    'status' => $status,
                    'admin_note' => $status !== 'pending' ? $this->getAdminNote($status) : null,
                    'processed_at' => in_array($status, ['processing', 'completed', 'rejected']) ? now()->subDays(rand(1, 30)) : null
                ]);
            }
        }
        
        $this->command->info('Withdrawal requests seeded successfully!');
    }
    
    private function getPaymentDetails(string $method): array
    {
        switch ($method) {
            case 'bank':
                return [
                    'account_number' => 'ACC' . rand(10000000, 99999999),
                    'routing_number' => 'RT' . rand(10000, 99999),
                    'account_holder_name' => 'John Doe'
                ];
            case 'paypal':
                return [
                    'email' => 'user' . rand(1, 100) . '@example.com'
                ];
            case 'stripe':
                return [
                    'stripe_account_id' => 'acct_' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT)
                ];
            case 'crypto':
                return [
                    'wallet_address' => '0x' . bin2hex(random_bytes(20)),
                    'currency' => 'ETH'
                ];
            default:
                return [];
        }
    }
    
    private function getAdminNote(string $status): string
    {
        $notes = [
            'processing' => 'Payment is being processed.',
            'completed' => 'Withdrawal completed successfully.',
            'rejected' => 'Insufficient funds in account.'
        ];
        
        return $notes[$status] ?? '';
    }
}