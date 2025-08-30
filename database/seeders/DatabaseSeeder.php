<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Сначала создаем пользователей
            UserSeeder::class,

            // Затем базовые настройки системы
            CompanySeeder::class,
            BonusSettingSeeder::class,

            // Справочники и типы
            PaymentTypeSeeder::class,
            ServiceTypeSeeder::class,
            OrderStatusSeeder::class,
            DeliveryTypeSeeder::class,
            EquipmentTypeSeeder::class,

            // Контент
            FaqSeeder::class,
        ]);
    }
}
