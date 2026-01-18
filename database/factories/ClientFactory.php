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
        // Генерируем телефон в формате +7 (999) 123-45-67
        $phone = '+7 (' . $this->faker->numerify('###') . ') ' . $this->faker->numerify('###-##-##');
        
        return [
            'full_name' => $this->faker->name(),
            'phone' => $phone,
            'email' => $this->faker->unique()->safeEmail(),
            'telegram' => $this->faker->userName(), // Без @, как в CreateClient
            'birth_date' => $this->faker->optional()->date('Y-m-d', '-18 years'),
            'delivery_address' => $this->faker->optional()->address(),
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
