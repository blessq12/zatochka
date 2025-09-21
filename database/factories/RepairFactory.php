<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Repair>
 */
class RepairFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'problem_description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 500, 5000),
            'status' => $this->faker->randomElement(['diagnosis', 'in_progress', 'waiting_parts', 'testing', 'completed', 'cancelled']),
            'comments' => $this->faker->optional()->sentence(),
            'completed_works' => $this->faker->optional()->randomElements([
                'Замена мотора',
                'Проверка проводки',
                'Чистка деталей',
                'Калибровка',
                'Тестирование'
            ], $this->faker->numberBetween(1, 3)),
        ];
    }
}