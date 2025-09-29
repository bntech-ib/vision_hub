<?php

namespace Database\Factories;

use App\Models\UserPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPackageFactory extends Factory
{
    protected $model = UserPackage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' Package',
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomElement([0, 9.99, 19.99, 29.99, 49.99]),
            'features' => $this->faker->randomElements([
                'Feature 1',
                'Feature 2',
                'Feature 3',
                'Feature 4',
                'Feature 5'
            ], $this->faker->numberBetween(2, 5)),
            'ad_views_limit' => $this->faker->randomElement([null, 100, 500, 1000, 5000]),
            'course_access_limit' => $this->faker->randomElement([null, 1, 5, 10, 20]),
            'marketplace_access' => $this->faker->boolean(),
            'brain_teaser_access' => $this->faker->boolean(),
            'duration_days' => $this->faker->randomElement([null, 30, 90, 180, 365]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
            'name' => 'Free Package',
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomElement([19.99, 29.99, 49.99, 99.99]),
            'name' => 'Premium Package',
            'marketplace_access' => true,
            'brain_teaser_access' => true,
        ]);
    }
}