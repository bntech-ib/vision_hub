<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WithdrawalRequest;

class UpdateWithdrawalRequestsPaymentMethodIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing withdrawal requests to set payment_method_id
        // For now, we'll assume all existing requests are from wallet balance (ID = 1)
        WithdrawalRequest::whereNull('payment_method_id')
            ->update(['payment_method_id' => 1]);
    }
}