<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['order', 'service', 'general']),
            'user_id' => Client::factory(),
            'order_id' => Order::factory(),
            'entity_id' => null,
            'entity_type' => null,
            'rating' => fake()->numberBetween(1, 5),
            'is_approved' => fake()->boolean(80),
            'comment' => fake()->paragraph(),
            'source' => fake()->randomElement(['website', 'telegram', 'phone', 'in_person']),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'reply' => fake()->optional()->sentence(),
            'metadata' => [
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'submitted_at' => fake()->dateTimeBetween('-1 month', 'now'),
            ],
        ];
    }

    /**
     * Indicate that the review is for order.
     */
    public function orderReview(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'order',
        ]);
    }

    /**
     * Indicate that the review is for service.
     */
    public function serviceReview(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'service',
        ]);
    }

    /**
     * Indicate that the review is approved.
     */
    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'approved',
            'is_approved' => true,
        ]);
    }

    /**
     * Indicate that the review is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'is_approved' => false,
        ]);
    }

    /**
     * Indicate that the review is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'rejected',
            'is_approved' => false,
        ]);
    }

    /**
     * Indicate that the review has high rating.
     */
    public function highRating(): static
    {
        return $this->state(fn(array $attributes) => [
            'rating' => fake()->numberBetween(4, 5),
        ]);
    }

    /**
     * Indicate that the review has low rating.
     */
    public function lowRating(): static
    {
        return $this->state(fn(array $attributes) => [
            'rating' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * Indicate that the review is from website.
     */
    public function fromWebsite(): static
    {
        return $this->state(fn(array $attributes) => [
            'source' => 'website',
        ]);
    }

    /**
     * Indicate that the review is from telegram.
     */
    public function fromTelegram(): static
    {
        return $this->state(fn(array $attributes) => [
            'source' => 'telegram',
        ]);
    }
}
