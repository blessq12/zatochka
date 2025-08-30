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
        // Создаем пользователя-менеджера для CRM панели
        User::firstOrCreate(
            [
                'email' => 'manager@zatochkatsk.ru',
            ],
            [
                'name' => 'Менеджер',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ]
        );

        // Создаем пользователя-мастера для панели мастерской
        User::firstOrCreate(
            [
                'email' => 'master@zatochkatsk.ru',
            ],
            [
                'name' => 'Мастер',
                'password' => Hash::make('password'),
                'role' => 'master',
                'email_verified_at' => now(),
            ]
        );
    }
}
