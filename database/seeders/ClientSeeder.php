<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Создает 10 тестовых клиентов с бонусными аккаунтами
     */
    public function run(): void
    {
        // Используем фабрику для создания клиентов
        // Бонусные аккаунты создаются автоматически через событие created в модели Client
        Client::factory()
            ->count(10)
            ->create();

        $this->command->info('Создано 10 клиентов с бонусными аккаунтами');
    }
}
