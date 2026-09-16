<?php

namespace Database\Factories;

use App\Infrastructure\Identity\Model\ManagerModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<ManagerModel>
 */
class ManagerFactory extends Factory
{
    protected $model = ManagerModel::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1000, 999999),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}
