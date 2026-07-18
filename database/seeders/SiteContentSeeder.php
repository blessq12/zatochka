<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanyProfileSeeder::class,
            SiteContactsSeeder::class,
            DeliveryInfoSeeder::class,
            WorkScheduleSeeder::class,
            ServicePriceListSeeder::class,
            FaqCatalogSeeder::class,
        ]);
    }
}
