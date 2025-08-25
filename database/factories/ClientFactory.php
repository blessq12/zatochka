<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'telegram' => fake()->optional()->userName(),
            'birth_date' => fake()->optional()->date(),
            'delivery_address' => fake()->optional()->address(),
            'telegram_verified_at' => fake()->optional()->dateTime(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the client is telegram verified.
     */
    public function telegramVerified(): static
    {
        return $this->state(fn(array $attributes) => [
            'telegram_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the client is not telegram verified.
     */
    public function telegramUnverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'telegram_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the client has telegram username.
     */
    public function withTelegram(): static
    {
        return $this->state(fn(array $attributes) => [
            'telegram' => fake()->userName(),
        ]);
    }
}
