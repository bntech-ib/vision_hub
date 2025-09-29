<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instructor_id' => User::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'category' => $this->faker->randomElement(['technology', 'business', 'design', 'marketing']),
            'difficulty' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'price' => $this->faker->randomFloat(2, 1000, 50000),
            'duration_hours' => $this->faker->numberBetween(1, 100),
            'thumbnail_url' => $this->faker->imageUrl(),
            'curriculum' => [],
            'tags' => [],
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'rating' => $this->faker->randomFloat(2, 0, 5),
            'total_enrollments' => $this->faker->numberBetween(0, 1000),
            'view_count' => $this->faker->numberBetween(0, 5000),
        ];
    }
}