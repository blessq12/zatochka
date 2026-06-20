<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            IdentitySeeder::class,
            WarehouseSeeder::class,
            EquipmentSeeder::class,
            ClientPortalSeeder::class,
            DemoOrderSeeder::class,
        ]);
    }
}
