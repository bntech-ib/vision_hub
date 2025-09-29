<?php

namespace Database\Factories;

use App\Models\SponsoredPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SponsoredPost>
 */
class SponsoredPostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SponsoredPost::class;

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
            'category' => $this->faker->randomElement(['technology', 'business', 'lifestyle', 'travel']),
            'budget' => $this->faker->randomFloat(2, 5000, 50000),
            'status' => $this->faker->randomElement(['draft', 'active', 'inactive', 'completed']),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'user_id' => User::factory(),
        ];
    }
}