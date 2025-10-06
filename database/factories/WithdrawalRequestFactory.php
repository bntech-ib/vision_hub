<?php

namespace Database\Factories;

use App\Models\WithdrawalRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WithdrawalRequestFactory extends Factory
{
    protected $model = WithdrawalRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->randomNumber(3),
            'payment_method' => $this->faker->randomElement(['Wallet Balance', 'Referral Earnings']),
            'payment_method_id' => $this->faker->randomElement([1, 2]),
            'payment_details' => [
                'accountName' => $this->faker->name,
                'accountNumber' => $this->faker->bankAccountNumber,
                'bankName' => $this->faker->company
            ],
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed']),
            'processed_at' => $this->faker->optional()->dateTime(),
            'admin_note' => $this->faker->optional()->sentence,
        ];
    }
}