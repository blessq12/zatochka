<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class DeliveryInfoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('site_delivery_infos')->updateOrInsert(
            ['id' => 1],
            [
                'free_conditions' => json_encode([
                    'От 6 маникюрных инструментов',
                    'От 3 парикмахерских/грумерских/барберских инструментов',
                    'Любой аппарат в ремонт',
                ], JSON_UNESCAPED_UNICODE),
                'advantages' => json_encode([
                    [
                        'title' => 'Безопасная упаковка',
                        'description' => 'Используем специальную упаковку для защиты ваших инструментов',
                    ],
                    [
                        'title' => 'Гарантия качества',
                        'description' => 'Несем ответственность за сохранность ваших инструментов',
                    ],
                    [
                        'title' => 'Курьер забирает заказ',
                        'description' => 'с 13:00 до 17:00 часов в дни работы мастерской (пн, вт, ср, пт, сб) чт и вс всегда выходной.',
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }
}
