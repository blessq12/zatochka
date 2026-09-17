<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ManagerSeeder::class,
            ActorsSeeder::class,
            SiteContentSeeder::class,
            WarehouseSeeder::class,
            EquipmentSeeder::class,
            DemoOrdersSeeder::class,
        ]);
    }
}
