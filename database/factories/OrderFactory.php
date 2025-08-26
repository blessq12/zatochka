<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
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
            'order_number' => 'Z' . date('Ymd') . '-' . Str::random(6),
            'service_type' => fake()->randomElement(['sharpening', 'repair']),
            'tool_type' => fake()->randomElement(['manicure', 'hair', 'grooming', 'clipper', 'dryer']),
            'equipment_name' => fake()->optional()->words(3, true),
            'problem_description' => fake()->optional()->sentence(),
            'needs_delivery' => fake()->boolean(),
            'delivery_address' => fake()->optional()->address(),
            'urgency' => fake()->randomElement(['normal', 'urgent', 'express']),
            'work_description' => fake()->optional()->paragraph(),
            'tools_photos' => fake()->optional()->imageUrl(),
            'needs_consultation' => fake()->boolean(),
            'total_tools_count' => fake()->numberBetween(1, 10),
            'is_paid' => fake()->boolean(),
            'is_ready_for_pickup' => fake()->boolean(),
            'quality_survey_sent' => fake()->boolean(),
            'review_request_sent' => fake()->boolean(),
            'ready_at' => fake()->optional()->dateTime(),
            'paid_at' => fake()->optional()->dateTime(),
            'status' => fake()->randomElement(['new', 'in_progress', 'ready', 'completed', 'cancelled']),
            'total_amount' => fake()->randomFloat(2, 500, 5000),
            'discount_percent' => fake()->randomFloat(2, 0, 20),
            'discount_amount' => fake()->randomFloat(2, 0, 500),
            'final_price' => fake()->optional()->randomFloat(2, 500, 5000),
            'used_materials' => fake()->optional()->words(3, true),
            'cost_price' => fake()->optional()->randomFloat(2, 100, 2000),
            'profit' => fake()->optional()->randomFloat(2, 100, 3000),
        ];
    }

    /**
     * Indicate that the order is for sharpening service.
     */
    public function sharpening(): static
    {
        return $this->state(fn(array $attributes) => [
            'service_type' => 'sharpening',
            'tool_type' => fake()->randomElement(['manicure', 'hair', 'grooming']),
            'equipment_name' => null,
            'problem_description' => null,
        ]);
    }

    /**
     * Indicate that the order is for repair service.
     */
    public function repair(): static
    {
        return $this->state(fn(array $attributes) => [
            'service_type' => 'repair',
            'tool_type' => fake()->randomElement(['clipper', 'dryer']),
            'equipment_name' => fake()->words(3, true),
            'problem_description' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the order needs delivery.
     */
    public function withDelivery(): static
    {
        return $this->state(fn(array $attributes) => [
            'needs_delivery' => true,
            'delivery_address' => fake()->address(),
        ]);
    }

    /**
     * Indicate that the order is paid.
     */
    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_paid' => true,
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the order is ready for pickup.
     */
    public function ready(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_ready_for_pickup' => true,
            'ready_at' => now(),
            'status' => 'ready',
        ]);
    }
}
