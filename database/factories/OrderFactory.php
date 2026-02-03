<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'branch_id' => Branch::factory(),
            'manager_id' => User::factory(),
            'type' => $this->faker->randomElement([
                Order::TYPE_REPAIR,
                Order::TYPE_SHARPENING,
                Order::TYPE_DIAGNOSTIC,
                Order::TYPE_REPLACEMENT,
                Order::TYPE_MAINTENANCE,
                Order::TYPE_CONSULTATION,
                Order::TYPE_WARRANTY,
            ]),
            'status' => $this->faker->randomElement([
                Order::STATUS_NEW,
                Order::STATUS_IN_WORK,
                Order::STATUS_WAITING_PARTS,
                Order::STATUS_READY,
                Order::STATUS_ISSUED,
                Order::STATUS_CANCELLED,
            ]),
            'urgency' => $this->faker->randomElement([
                Order::URGENCY_NORMAL,
                Order::URGENCY_URGENT,
            ]),
            'price' => $this->faker->randomFloat(2, 100, 5000),
            'internal_notes' => $this->faker->sentence(),
            'problem_description' => $this->faker->paragraph(),
            'is_deleted' => false,
        ];
    }

    /**
     * Indicate that the order is new.
     */
    public function newOrder(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_NEW,
        ]);
    }

    /**
     * Indicate that the order is in work.
     */
    public function inWork(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_IN_WORK,
        ]);
    }

    /**
     * Indicate that the order is ready.
     */
    public function ready(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_READY,
        ]);
    }

    /**
     * Indicate that the order is issued.
     */
    public function issued(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_ISSUED,
        ]);
    }

    /**
     * Indicate that the order is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn(array $attributes) => [
            'urgency' => Order::URGENCY_URGENT,
        ]);
    }

    /**
     * Indicate that the order is for sharpening.
     */
    public function sharpening(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Order::TYPE_SHARPENING,
        ]);
    }

    /**
     * Indicate that the order is for repair.
     */
    public function repair(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Order::TYPE_REPAIR,
        ]);
    }
}
