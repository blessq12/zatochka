<?php

namespace Database\Factories;

use App\Models\ClientBonus;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientBonus>
 */
class ClientBonusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientBonus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'total_earned' => fake()->randomFloat(2, 100, 5000),
            'total_spent' => fake()->randomFloat(2, 0, 3000),
            'current_balance' => fake()->randomFloat(2, 0, 2000),
            'expired_balance' => fake()->randomFloat(2, 0, 500),
            'last_earned_at' => fake()->optional()->dateTimeBetween('-6 months', 'now'),
            'last_spent_at' => fake()->optional()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Indicate that the client has high bonus balance.
     */
    public function highBalance(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_earned' => fake()->randomFloat(2, 3000, 8000),
            'current_balance' => fake()->randomFloat(2, 1500, 4000),
        ]);
    }

    /**
     * Indicate that the client has low bonus balance.
     */
    public function lowBalance(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_earned' => fake()->randomFloat(2, 100, 1000),
            'current_balance' => fake()->randomFloat(2, 0, 500),
        ]);
    }

    /**
     * Indicate that the client has no bonus balance.
     */
    public function noBalance(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_earned' => fake()->randomFloat(2, 100, 1000),
            'total_spent' => fake()->randomFloat(2, 100, 1000),
            'current_balance' => 0,
        ]);
    }
}
