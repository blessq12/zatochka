<?php

namespace Database\Factories;

use App\Models\Repair;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Repair>
 */
class RepairFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Repair::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'branch_id' => Branch::factory(),
            'handle_number' => 'R' . fake()->unique()->numberBetween(10000, 99999),
            'description' => fake()->randomElement([
                'Замена двигателя',
                'Ремонт электроники',
                'Замена лезвий',
                'Ремонт зарядного устройства',
                'Очистка и смазка механизмов',
                'Замена аккумулятора',
                'Ремонт системы охлаждения',
                'Замена шнура питания'
            ]),
            'cost' => fake()->randomFloat(2, 500, 3000),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
        ];
    }

    /**
     * Indicate that the repair is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the repair is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * Indicate that the repair is completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the repair is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
