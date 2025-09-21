<?php

namespace Database\Factories;

use App\Models\StockCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockItem>
 */
class StockItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => StockCategory::factory(),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'sku' => $this->faker->unique()->bothify('SKU-###-???'),
            'retail_price' => $this->faker->randomFloat(2, 100, 5000),
            'quantity' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
