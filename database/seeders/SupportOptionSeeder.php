<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SupportOption;

class SupportOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supportOptions = [
            [
                'title' => 'General Support',
                'description' => 'Get help with general questions and platform usage',
                'icon' => 'helpCircle',
                'whatsapp_number' => '+1234567890',
                'whatsapp_message' => 'Hello, I need general support',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Account Issues',
                'description' => 'Assistance with login, registration, and account security',
                'icon' => 'person',
                'whatsapp_number' => '+1234567891',
                'whatsapp_message' => 'Hello, I need help with my account',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Payment Support',
                'description' => 'Help with deposits, withdrawals, and payment methods',
                'icon' => 'card',
                'whatsapp_number' => '+1234567892',
                'whatsapp_message' => 'Hello, I need payment assistance',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Technical Support',
                'description' => 'Troubleshoot technical issues and platform bugs',
                'icon' => 'shield',
                'whatsapp_number' => '+1234567893',
                'whatsapp_message' => 'Hello, I am experiencing technical issues',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Billing Inquiries',
                'description' => 'Questions about packages, pricing, and billing',
                'icon' => 'wallet',
                'whatsapp_number' => '+1234567894',
                'whatsapp_message' => 'Hello, I have a billing question',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($supportOptions as $supportOption) {
            SupportOption::updateOrCreate(
                ['title' => $supportOption['title']],
                $supportOption
            );
        }
    }
}