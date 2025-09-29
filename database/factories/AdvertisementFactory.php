<?php

namespace Database\Factories;

use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Advertisement>
 */
class AdvertisementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Advertisement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'image_url' => $this->faker->imageUrl(),
            'target_url' => $this->faker->url,
            'category' => $this->faker->randomElement(['technology', 'business', 'education', 'entertainment']),
            'budget' => $this->faker->randomFloat(2, 10000, 100000),
            'reward_amount' => $this->faker->randomFloat(2, 10, 100),
            'spent' => $this->faker->randomFloat(2, 0, 50000),
            'impressions' => $this->faker->numberBetween(0, 10000),
            'clicks' => $this->faker->numberBetween(0, 1000),
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => $this->faker->randomElement(['active', 'paused', 'completed', 'expired']),
            'advertiser_id' => User::factory(),
            'targeting' => [],
        ];
    }
}