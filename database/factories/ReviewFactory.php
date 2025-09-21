<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'order_id' => Order::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->paragraph(),
            'is_visible' => true,
        ];
    }

    /**
     * Indicate that the review has a high rating.
     */
    public function highRating(): static
    {
        return $this->state(fn(array $attributes) => [
            'rating' => $this->faker->numberBetween(4, 5),
        ]);
    }

    /**
     * Indicate that the review has a low rating.
     */
    public function lowRating(): static
    {
        return $this->state(fn(array $attributes) => [
            'rating' => $this->faker->numberBetween(1, 2),
        ]);
    }

    /**
     * Indicate that the review is not visible.
     */
    public function hidden(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_visible' => false,
        ]);
    }
}
