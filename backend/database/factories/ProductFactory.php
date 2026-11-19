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
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'barcode' => fake()->unique()->ean13(),
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'unit' => 'pc',
            'price' => fake()->numberBetween(1000, 50000),
            'cost' => fake()->numberBetween(500, 900),
            'tax_class' => 'vatable',
            'track_stock' => true,
            'reorder_level' => 5,
            'active' => true,
        ];
    }
}
