<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Prod-demo данные по BC (порядок = зависимости).
 *
 * Company → Pricing → Identity → Warehouse → Equipment → ClientPortal → OrderFulfillment
 *
 * Аккаунты: root@root.com / master@master.com / ivan.petrov@zatochka.local / +79001234567 — password
 */
class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            PricingSeeder::class,
            IdentitySeeder::class,
            WarehouseSeeder::class,
            EquipmentSeeder::class,
            ClientPortalSeeder::class,
            DemoOrderSeeder::class,
        ]);
    }
}
