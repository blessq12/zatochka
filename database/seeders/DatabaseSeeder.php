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
            CompanySeeder::class,
            FaqSeeder::class,
            PaymentTypeSeeder::class,
            ServiceTypeSeeder::class,
            OrderStatusSeeder::class,
            DeliveryTypeSeeder::class,
            EquipmentTypeSeeder::class,
            BonusSettingSeeder::class,
        ]);
    }
}
