<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 100, 10000),
            'category' => $this->faker->randomElement(['electronics', 'clothing', 'books', 'home', 'sports']),
            'images' => [],
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'specifications' => [],
            'status' => $this->faker->randomElement(['active', 'inactive', 'out_of_stock']),
            'seller_id' => User::factory(),
            'rating' => $this->faker->randomFloat(2, 0, 5),
            'total_reviews' => $this->faker->numberBetween(0, 500),
            'is_featured' => $this->faker->boolean,
            'view_count' => $this->faker->numberBetween(0, 2000),
        ];
    }
}