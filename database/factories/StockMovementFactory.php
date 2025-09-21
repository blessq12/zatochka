<?php

namespace Database\Factories;

use App\Models\Repair;
use App\Models\StockItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'repair_id' => Repair::factory(),
            'stock_item_id' => StockItem::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'unit_price' => $this->faker->randomFloat(2, 100, 1000),
            'total_amount' => $this->faker->randomFloat(2, 500, 5000),
            'description' => $this->faker->optional()->sentence(),
            'movement_type' => $this->faker->randomElement(['in', 'out']),
            'movement_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_by' => User::factory(),
        ];
    }
}