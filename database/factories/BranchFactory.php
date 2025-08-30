<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement([
                'Центральный офис',
                'Филиал на Ленина',
                'Филиал на Пушкина',
                'Филиал на Гагарина',
                'Филиал на Мира'
            ]),
            'code' => 'BR' . fake()->unique()->numberBetween(1000, 9999),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'working_hours' => 'Пн-Пт: 9:00-18:00, Сб: 10:00-16:00',
            'latitude' => fake()->latitude(55.0, 56.0),
            'longitude' => fake()->longitude(37.0, 38.0),
            'description' => fake()->optional()->sentence(),
            'additional_data' => [
                'manager' => fake()->name(),
                'capacity' => fake()->numberBetween(50, 200),
                'services' => ['sharpening', 'repair', 'consultation']
            ],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the branch is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the branch is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }
}
