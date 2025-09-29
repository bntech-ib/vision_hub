<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds for users.
     */
    public function run(): void
    {
        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@visionhub.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'full_name' => 'VisionHub Administrator',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'wallet_balance' => 1000.00,
                'referral_code' => 'ADM001',
            ]
        );

        // Create demo regular user
        $demoUser = User::firstOrCreate(
            ['email' => 'demo@visionhub.com'],
            [
                'name' => 'Demo User',
                'username' => 'demo',
                'full_name' => 'John Demo',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_admin' => false,
                'wallet_balance' => 250.50,
                'referral_code' => 'DEM001',
            ]
        );

        $this->command->info('Admin user created or already exists:');
        $this->command->info('- Email: admin@visionhub.com');
        $this->command->info('- Password: admin123');
        $this->command->info('');
        $this->command->info('Demo user created or already exists:');
        $this->command->info('- Email: demo@visionhub.com');
        $this->command->info('- Password: password123');
    }
}