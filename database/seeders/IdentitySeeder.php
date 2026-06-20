<?php

namespace Database\Seeders;

use App\Domain\Identity\Enum\UserRole;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class IdentitySeeder extends Seeder
{
    public const MASTER_EMAIL = 'master@master.com';

    public const MANAGER_EMAIL = 'root@root.com';

    public const DEMO_PASSWORD = 'password';

    public function run(): void
    {
        $password = Hash::make(self::DEMO_PASSWORD);

        $masters = [
            [
                'email' => self::MASTER_EMAIL,
                'name' => 'Демо',
                'surname' => 'Мастер',
                'phone' => '+79000000001',
            ],
            [
                'email' => 'ivan.petrov@zatochka.local',
                'name' => 'Иван',
                'surname' => 'Петров',
                'phone' => '+79000000011',
            ],
            [
                'email' => 'sergey.orlov@zatochka.local',
                'name' => 'Сергей',
                'surname' => 'Орлов',
                'phone' => '+79000000012',
            ],
            [
                'email' => 'anna.kuznetsova@zatochka.local',
                'name' => 'Анна',
                'surname' => 'Кузнецова',
                'phone' => '+79000000013',
            ],
        ];

        foreach ($masters as $master) {
            UserModel::query()->updateOrCreate(
                ['email' => $master['email']],
                [
                    'name' => $master['name'],
                    'surname' => $master['surname'],
                    'role' => UserRole::Master,
                    'phone' => $master['phone'],
                    'password' => $password,
                ],
            );
        }

        $managers = [
            [
                'email' => self::MANAGER_EMAIL,
                'name' => 'Root',
                'surname' => 'Admin',
                'phone' => '+79000000002',
            ],
            [
                'email' => 'olga.manager@zatochka.local',
                'name' => 'Ольга',
                'surname' => 'Смирнова',
                'phone' => '+79000000021',
            ],
            [
                'email' => 'dmitry.manager@zatochka.local',
                'name' => 'Дмитрий',
                'surname' => 'Волков',
                'phone' => '+79000000022',
            ],
        ];

        foreach ($managers as $manager) {
            UserModel::query()->updateOrCreate(
                ['email' => $manager['email']],
                [
                    'name' => $manager['name'],
                    'surname' => $manager['surname'],
                    'role' => UserRole::Manager,
                    'phone' => $manager['phone'],
                    'password' => $password,
                ],
            );
        }
    }
}
