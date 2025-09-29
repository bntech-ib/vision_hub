<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'original_filename' => $this->faker->word . '.jpg',
            'file_path' => 'images/' . $this->faker->uuid . '.jpg',
            'file_hash' => $this->faker->sha256,
            'mime_type' => 'image/jpeg',
            'file_size' => $this->faker->numberBetween(1000, 5000000),
            'width' => $this->faker->numberBetween(100, 2000),
            'height' => $this->faker->numberBetween(100, 2000),
            'project_id' => Project::factory(),
            'uploaded_by' => User::factory(),
            'metadata' => [],
            'status' => $this->faker->randomElement(['uploaded', 'processing', 'processed', 'error']),
            'processing_notes' => $this->faker->sentence,
        ];
    }
}