<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SupportOption;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class SupportOptionFactory extends Factory
{
    protected $model = SupportOption::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'avatar' => null, // Will be set manually when needed
            'whatsapp_number' => $this->faker->numerify('+1##########'),
            'whatsapp_message' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}