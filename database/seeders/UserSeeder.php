<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            [
                'email' => 'admin@zatochka.org',
            ],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Создаем тестового пользователя для демо
        User::firstOrCreate(
            [
                'email' => 'demo@zatochka.org',
            ],
            [
                'name' => 'Демо Пользователь',
                'password' => Hash::make('demo123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}
