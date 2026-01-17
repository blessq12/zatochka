<?php

namespace Database\Seeders;

use App\Models\PriceItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Данные для заточки
        $sharpeningData = [
            [
                'category_title' => 'ИНСТРУМЕНТЫ ДЛЯ МАНИКЮРА / ПЕДИКЮРА',
                'items' => [
                    ['name' => 'Ножницы/кусачки', 'price' => '350', 'sort_order' => 1],
                    ['name' => 'Пушеры', 'price' => '150', 'sort_order' => 2],
                ],
            ],
            [
                'category_title' => 'ДЛЯ ПАРИКМАХЕРОВ / БАРБЕРОВ / ГРУМЕРОВ',
                'items' => [
                    ['name' => 'Любые ножницы', 'price' => '600', 'sort_order' => 1],
                    ['name' => 'Любые ножевые блоки', 'price' => '600', 'sort_order' => 2],
                ],
            ],
            [
                'category_title' => 'ДЛЯ БРОВИСТОВ / ЛЭШМЕЙКЕРОВ',
                'items' => [
                    ['name' => 'Пинцет', 'price' => '400', 'sort_order' => 1],
                ],
            ],
            [
                'category_title' => 'БЫТОВЫЕ / ПОРТНОВСКИЕ НОЖНИЦЫ',
                'items' => [
                    ['name' => 'Ножницы', 'price' => '300', 'sort_order' => 1],
                ],
            ],
        ];

        $categorySort = 1;
        foreach ($sharpeningData as $category) {
            $itemSort = 1;
            foreach ($category['items'] as $item) {
                PriceItem::firstOrCreate(
                    [
                        'service_type' => PriceItem::TYPE_SHARPENING,
                        'category_title' => $category['category_title'],
                        'name' => $item['name'],
                    ],
                    [
                        'description' => null,
                        'price' => $item['price'],
                        'sort_order' => $categorySort * 100 + $itemSort,
                        'is_active' => true,
                    ]
                );
                $itemSort++;
            }
            $categorySort++;
        }

        // Данные для ремонта
        $repairData = [
            [
                'category_title' => 'ЧИСТКА И ДИАГНОСТИКА',
                'items' => [
                    ['name' => 'Ручки/блока', 'price' => '500', 'description' => null, 'sort_order' => 1],
                ],
            ],
            [
                'category_title' => 'FIX PRICE',
                'items' => [
                    [
                        'name' => 'Поломки блока управления',
                        'price' => '1000',
                        'description' => '(Ремонт регулятора скорости, гнезда ручки, реверс, кроме замены трансформатора)',
                        'sort_order' => 1,
                    ],
                ],
            ],
            [
                'category_title' => 'ЗАМЕНА',
                'items' => [
                    ['name' => 'Трансформатора', 'price' => '4500', 'description' => null, 'sort_order' => 1],
                    ['name' => '2х подшипников (ДЕТАЛИ + работа)', 'price' => '1700', 'description' => null, 'sort_order' => 2],
                    ['name' => '4х подшипников (ДЕТАЛИ + работа)', 'price' => '2900', 'description' => null, 'sort_order' => 3],
                    ['name' => 'Щёток наши/ваши', 'price' => '800/400', 'description' => null, 'sort_order' => 4],
                ],
            ],
        ];

        $categorySort = 1;
        foreach ($repairData as $category) {
            foreach ($category['items'] as $item) {
                PriceItem::firstOrCreate(
                    [
                        'service_type' => PriceItem::TYPE_REPAIR,
                        'category_title' => $category['category_title'],
                        'name' => $item['name'],
                    ],
                    [
                        'description' => $item['description'] ?? null,
                        'price' => $item['price'],
                        'sort_order' => $categorySort * 100 + $item['sort_order'],
                        'is_active' => true,
                    ]
                );
            }
            $categorySort++;
        }
    }
}
