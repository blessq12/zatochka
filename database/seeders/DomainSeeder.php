<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Демо-данные по BC (порядок = зависимости).
 *
 * Company → Pricing → SiteSettings → Identity → Warehouse → Equipment → ClientPortal → OrderFulfillment
 */
class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            PricingSeeder::class,
            SiteSettingsSeeder::class,
            IdentitySeeder::class,
            WarehouseSeeder::class,
            EquipmentSeeder::class,
            ClientPortalSeeder::class,
            DemoOrderSeeder::class,
        ]);
    }
}
