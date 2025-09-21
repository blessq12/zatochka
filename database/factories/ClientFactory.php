<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'telegram' => '@' . $this->faker->userName(),
            'birth_date' => $this->faker->date(),
            'delivery_address' => $this->faker->address(),
            'password' => Hash::make('password'),
            'is_deleted' => false,
        ];
    }

    /**
     * Indicate that the client is deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_deleted' => true,
        ]);
    }
}
