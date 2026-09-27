<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(['Sembako', 'Minuman', 'Makanan', 'Kebersihan']),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 1000, 500000),
            'stock' => fake()->numberBetween(0, 100),
            'image' => null,
            'is_active' => true,
        ];
    }
}
