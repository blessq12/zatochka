<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ServicePriceListSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('site_price_items')->delete();
        DB::table('site_price_blocks')->delete();

        DB::table('site_price_blocks')->insert([
            [
                'id' => 1,
                'type' => 'sharpening',
                'title' => 'Заточка инструмента',
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'type' => 'repair',
                'title' => 'Ремонт аппаратов',
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('site_price_items')->insert([
            [
                'id' => 1,
                'price_block_id' => 1,
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
                'price_block_id' => 1,
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
                'price_block_id' => 1,
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
                'price_block_id' => 2,
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
                'price_block_id' => 2,
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
                'price_block_id' => 2,
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
                'price_block_id' => 2,
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
                'price_block_id' => 2,
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
                'price_block_id' => 2,
                'name' => 'провод',
                'price' => '1000',
                'prefix' => null,
                'description' => null,
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        foreach ([
            'site_price_block' => 'site_price_blocks',
            'site_price_item' => 'site_price_items',
        ] as $sequence => $table) {
            $maxId = (int) (DB::table($table)->max('id') ?? 0);
            DB::table('entity_id_sequences')->updateOrInsert(
                ['name' => $sequence],
                ['next_value' => $maxId + 1],
            );
        }
    }
}
