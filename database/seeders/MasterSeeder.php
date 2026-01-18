<?php

namespace Database\Seeders;

use App\Models\Master;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterSeeder extends Seeder
{
    /**
     * Создает 2 мастеров
     */
    public function run(): void
    {
        $masters = [
            [
                'name' => 'Александр',
                'surname' => 'Петров',
                'email' => 'master1@zatochka.ru',
                'phone' => '+7 (913) 123-45-67',
                'telegram_username' => 'master_petrov',
                'notifications_enabled' => true,
                'password' => Hash::make('password'),
                'is_deleted' => false,
            ],
            [
                'name' => 'Мария',
                'surname' => 'Иванова',
                'email' => 'master2@zatochka.ru',
                'phone' => '+7 (913) 234-56-78',
                'telegram_username' => 'master_ivanova',
                'notifications_enabled' => true,
                'password' => Hash::make('password'),
                'is_deleted' => false,
            ],
        ];

        foreach ($masters as $masterData) {
            Master::firstOrCreate(
                ['email' => $masterData['email']],
                $masterData
            );
        }

        $this->command->info('Создано 2 мастеров');
    }
}
