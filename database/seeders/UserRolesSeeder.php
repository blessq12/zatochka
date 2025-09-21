<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем root-пользователя с ролями manager и master
        $rootUser = User::firstOrCreate(
            ['email' => 'root@root.com'],
            [
                'name' => 'Администратор',
                'password' => bcrypt('password'),
                'is_deleted' => false,
                'role' => ['manager', 'master'],
            ]
        );
    }
}
