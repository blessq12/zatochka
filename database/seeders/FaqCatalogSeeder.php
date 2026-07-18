<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class FaqCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('site_faq_items')->delete();
        DB::table('site_faq_items')->insert([
            [
                'id' => 1,
                'question' => 'Как долго делается заточка?',
                'answer_lines' => json_encode([
                    'Стандартный срок заточки зависит от загруженности мастерской и количества инструмента.',
                    'Обычно работы выполняются в срок от 1 до 3 рабочих дней.',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'question' => 'Как долго ремонтируется аппарат?',
                'answer_lines' => json_encode([
                    'Стандартный срок ремонта аппарата — 2 рабочих дня мастерской + время на доставку.',
                    'В случае сложного ремонта и отсутствия запчастей сроки дополнительно согласовываются.',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'question' => 'Есть гарантия на заточку?',
                'answer_lines' => json_encode([
                    'Да, мы предоставляем гарантию при соблюдении рекомендаций по использованию инструмента.',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'question' => 'Есть гарантия на ремонт?',
                'answer_lines' => json_encode([
                    'На все виды ремонта действует гарантия 14 дней с момента выдачи инструмента.',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'question' => 'Как происходит доставка?',
                'answer_lines' => json_encode([
                    'Курьер забирает и привозит инструменты по согласованному адресу и времени.',
                    'Доставка доступна по условиям.',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'question' => 'Какие способы оплаты?',
                'answer_lines' => json_encode([
                    'Вы можете оплатить услуги наличными или по безналичному расчету (перевод, счет).',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $maxId = (int) (DB::table('site_faq_items')->max('id') ?? 0);
        DB::table('entity_id_sequences')->updateOrInsert(
            ['name' => 'site_faq_item'],
            ['next_value' => $maxId + 1],
        );
    }
}
