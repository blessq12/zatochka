<?php

namespace Database\Factories;

use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tool>
 */
class ToolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'equipment_type_id' => EquipmentType::factory(),
            'serial_number' => $this->faker->unique()->bothify('SN-###-???'),
            'brand' => $this->faker->company(),
            'model' => $this->faker->bothify('Model-###'),
            'description' => $this->faker->sentence(),
            'purchase_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'warranty_expiry' => $this->faker->dateTimeBetween('now', '+2 years'),
            'is_active' => true,
            'is_deleted' => false,
        ];
    }
}