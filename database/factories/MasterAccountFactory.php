<?php

namespace Database\Factories;

use App\Infrastructure\CRM\Model\MasterModel;
use App\Infrastructure\Identity\Model\MasterAccountModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<MasterAccountModel>
 */
class MasterAccountFactory extends Factory
{
    protected $model = MasterAccountModel::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1000, 999999),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (MasterAccountModel $account): void {
            MasterModel::query()->firstOrCreate(
                ['id' => $account->id],
                [
                    'name' => fake()->name(),
                    'email' => $account->email,
                ],
            );
        });
    }
}
