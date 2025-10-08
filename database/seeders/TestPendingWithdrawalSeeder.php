<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WithdrawalRequest;
use App\Models\User;
use App\Models\Transaction;

class TestPendingWithdrawalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a regular user for the withdrawal request
        $user = User::where('is_admin', false)->first();
        
        if (!$user) {
            $this->command->info('No regular user found. Creating a test user.');
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'wallet_balance' => 1000,
                'referral_earnings' => 500,
                'bank_account_holder_name' => 'Test User',
                'bank_account_number' => '1234567890',
                'bank_name' => 'Test Bank',
                'bank_account_bound_at' => now()
            ]);
        }
        
        // Create a pending withdrawal request from wallet balance
        $withdrawal = WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => 100,
            'payment_method' => 'Wallet Balance',
            'payment_method_id' => 1,
            'payment_details' => [
                'accountName' => $user->bank_account_holder_name,
                'accountNumber' => $user->bank_account_number,
                'bankName' => $user->bank_name
            ],
            'status' => 'pending'
        ]);
        
        // Create the associated transaction record
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal_request',
            'amount' => -100,
            'description' => 'Withdrawal requested - Wallet Balance',
            'status' => 'pending',
            'reference_type' => WithdrawalRequest::class,
            'reference_id' => $withdrawal->id
        ]);
        
        $this->command->info('Pending withdrawal request created successfully!');
        $this->command->info('Withdrawal ID: ' . $withdrawal->id);
        $this->command->info('User ID: ' . $user->id);
        $this->command->info('Amount: 100');
        $this->command->info('');
        $this->command->info('To approve this withdrawal, use the following command:');
        $this->command->info("curl -X PUT http://localhost/admin/withdrawals/{$withdrawal->id}/approve \\");
        $this->command->info('  -H "Content-Type: application/json" \\');
        $this->command->info('  -H "X-Requested-With: XMLHttpRequest" \\');
        $this->command->info('  -d \'{"notes": "Approved by admin", "transaction_id": "txn_12345"}\' \\');
        $this->command->info('  --cookie "laravel_session=YOUR_SESSION_COOKIE"');
        $this->command->info('');
        $this->command->info('To reject this withdrawal, use the following command:');
        $this->command->info("curl -X PUT http://localhost/admin/withdrawals/{$withdrawal->id}/reject \\");
        $this->command->info('  -H "Content-Type: application/json" \\');
        $this->command->info('  -H "X-Requested-With: XMLHttpRequest" \\');
        $this->command->info('  -d \'{"reason": "Invalid bank details"}\' \\');
        $this->command->info('  --cookie "laravel_session=YOUR_SESSION_COOKIE"');
    }
}