<?php

namespace Database\Seeders;

use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class IdentitySeeder extends Seeder
{
    public const MASTER_EMAIL = 'master@zatochka.local';

    public const MANAGER_EMAIL = 'manager@zatochka.local';

    public const DEMO_PASSWORD = 'password';

    public function run(): void
    {
        UserModel::query()->firstOrCreate(
            ['email' => self::MASTER_EMAIL],
            [
                'name' => 'Демо',
                'surname' => 'Мастер',
                'phone' => '+79000000001',
                'password' => Hash::make(self::DEMO_PASSWORD),
                'notifications_enabled' => true,
            ],
        );

        UserModel::query()->firstOrCreate(
            ['email' => self::MANAGER_EMAIL],
            [
                'name' => 'Демо',
                'surname' => 'Менеджер',
                'phone' => '+79000000002',
                'password' => Hash::make(self::DEMO_PASSWORD),
                'notifications_enabled' => false,
            ],
        );
    }
}
