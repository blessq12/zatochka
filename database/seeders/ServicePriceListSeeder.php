<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ServicePriceListSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('site_service_prices')->delete();
        DB::table('site_service_prices')->insert([
            [
                'id' => 1,
                'category' => 'sharpening',
                'name' => 'Маникюрный инструмент (1 шт.)',
                'price' => '400',
                'prefix' => null,
                'description' => null,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'category' => 'sharpening',
                'name' => 'Парикмахерские ножницы (1 пара)',
                'price' => '700',
                'prefix' => null,
                'description' => null,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'category' => 'sharpening',
                'name' => 'Грумерские / барберские ножницы (1 пара)',
                'price' => '700',
                'prefix' => null,
                'description' => null,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'category' => 'repair',
                'name' => 'Диагностика',
                'price' => '500',
                'prefix' => null,
                'description' => null,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'category' => 'repair',
                'name' => 'Замена подшипника',
                'price' => '1200',
                'prefix' => null,
                'description' => null,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'category' => 'repair',
                'name' => 'Замена приводного ремня',
                'price' => '900',
                'prefix' => null,
                'description' => null,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'category' => 'repair',
                'name' => 'Чистка и смазка',
                'price' => '700',
                'prefix' => null,
                'description' => null,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'category' => 'repair',
                'name' => '1 подшипник',
                'price' => '600',
                'prefix' => null,
                'description' => null,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 9,
                'category' => 'repair',
                'name' => 'провод',
                'price' => '1000',
                'prefix' => null,
                'description' => null,
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $maxId = (int) (DB::table('site_service_prices')->max('id') ?? 0);
        DB::table('entity_id_sequences')->updateOrInsert(
            ['name' => 'site_service_price'],
            ['next_value' => $maxId + 1],
        );
    }
}
