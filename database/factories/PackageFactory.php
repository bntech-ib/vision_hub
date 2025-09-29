<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Package;

class PackageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Package::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'features' => $this->faker->words(5),
            'ad_views_limit' => $this->faker->numberBetween(10, 1000),
            'course_access_limit' => $this->faker->numberBetween(1, 50),
            'marketplace_access' => $this->faker->boolean(80),
            'brain_teaser_access' => $this->faker->boolean(80),
            'duration_days' => $this->faker->numberBetween(1, 365),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }
}