<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'transaction_id' => 'TXN_' . strtoupper($this->faker->unique()->lexify('??????????')),
            'type' => $this->faker->randomElement(['earning', 'withdrawal', 'purchase', 'refund']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'reference_type' => null,
            'reference_id' => null,
            'metadata' => null,
        ];
    }

    public function earning(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'earning',
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'completed',
        ]);
    }

    public function withdrawal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'withdrawal',
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'completed',
        ]);
    }

    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'purchase',
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'completed',
        ]);
    }

    public function refund(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'refund',
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'completed',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
        ]);
    }
}