<?php

namespace Database\Seeders;

use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем категорию "Запчасти"
        $partsCategory = WarehouseCategory::firstOrCreate(
            ['slug' => 'zapchasti'],
            [
                'name' => 'Запчасти',
                'description' => 'Запчасти для ремонта инструментов',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        // Создаем категорию "Расходные материалы"
        $consumablesCategory = WarehouseCategory::firstOrCreate(
            ['slug' => 'rashodnye-materialy'],
            [
                'name' => 'Расходные материалы',
                'description' => 'Расходные материалы для обслуживания и ремонта',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        // Запчасти (25-30 товаров)
        $parts = [
            ['name' => 'Нож для машинки Wahl', 'price' => 850.00, 'quantity' => 15, 'min_quantity' => 3, 'unit' => 'шт', 'description' => 'Нож для машинки Wahl Professional'],
            ['name' => 'Нож для машинки Oster', 'price' => 920.00, 'quantity' => 12, 'min_quantity' => 3, 'unit' => 'шт', 'description' => 'Нож для машинки Oster Classic 76'],
            ['name' => 'Нож для машинки Andis', 'price' => 780.00, 'quantity' => 18, 'min_quantity' => 3, 'unit' => 'шт', 'description' => 'Нож для машинки Andis'],
            ['name' => 'Подвижный нож', 'price' => 650.00, 'quantity' => 20, 'min_quantity' => 5, 'unit' => 'шт', 'description' => 'Подвижный нож универсальный'],
            ['name' => 'Ножевой блок', 'price' => 1200.00, 'quantity' => 8, 'min_quantity' => 2, 'unit' => 'шт', 'description' => 'Ножевой блок для машинки'],
            ['name' => 'Мотор для машинки', 'price' => 3500.00, 'quantity' => 5, 'min_quantity' => 1, 'unit' => 'шт', 'description' => 'Электродвигатель для машинки'],
            ['name' => 'Подшипник 608ZZ', 'price' => 120.00, 'quantity' => 50, 'min_quantity' => 10, 'unit' => 'шт', 'description' => 'Подшипник шариковый 608ZZ'],
            ['name' => 'Подшипник 625ZZ', 'price' => 95.00, 'quantity' => 45, 'min_quantity' => 10, 'unit' => 'шт', 'description' => 'Подшипник шариковый 625ZZ'],
            ['name' => 'Подшипник 626ZZ', 'price' => 110.00, 'quantity' => 40, 'min_quantity' => 10, 'unit' => 'шт', 'description' => 'Подшипник шариковый 626ZZ'],
            ['name' => 'Щетки угольные', 'price' => 350.00, 'quantity' => 25, 'min_quantity' => 5, 'unit' => 'пара', 'description' => 'Угольные щетки для электродвигателя'],
            ['name' => 'Пружина возвратная', 'price' => 80.00, 'quantity' => 30, 'min_quantity' => 10, 'unit' => 'шт', 'description' => 'Пружина возвратная для машинки'],
            ['name' => 'Кнопка включения', 'price' => 250.00, 'quantity' => 15, 'min_quantity' => 3, 'unit' => 'шт', 'description' => 'Кнопка включения/выключения'],
            ['name' => 'Переключатель скорости', 'price' => 450.00, 'quantity' => 10, 'min_quantity' => 2, 'unit' => 'шт', 'description' => 'Переключатель скорости для машинки'],
            ['name' => 'Регулятор скорости', 'price' => 1200.00, 'quantity' => 6, 'min_quantity' => 1, 'unit' => 'шт', 'description' => 'Регулятор скорости вращения'],
            ['name' => 'Трансформатор', 'price' => 2800.00, 'quantity' => 4, 'min_quantity' => 1, 'unit' => 'шт', 'description' => 'Трансформатор для блока управления'],
            ['name' => 'Провод сетевой', 'price' => 180.00, 'quantity' => 20, 'min_quantity' => 5, 'unit' => 'шт', 'description' => 'Сетевой провод с вилкой'],
            ['name' => 'Корпус машинки', 'price' => 1500.00, 'quantity' => 8, 'min_quantity' => 2, 'unit' => 'шт', 'description' => 'Корпус для машинки'],
            ['name' => 'Ручка машинки', 'price' => 320.00, 'quantity' => 12, 'min_quantity' => 3, 'unit' => 'шт', 'description' => 'Ручка для машинки'],
            ['name' => 'Шестерня ведущая', 'price' => 450.00, 'quantity' => 15, 'min_quantity' => 3, 'unit' => 'шт', 'description' => 'Ведущая шестерня'],
            ['name' => 'Шестерня ведомая', 'price' => 380.00, 'quantity' => 15, 'min_quantity' => 3, 'unit' => 'шт', 'description' => 'Ведомая шестерня'],
            ['name' => 'Вал ротора', 'price' => 650.00, 'quantity' => 10, 'min_quantity' => 2, 'unit' => 'шт', 'description' => 'Вал ротора двигателя'],
            ['name' => 'Статор', 'price' => 1800.00, 'quantity' => 5, 'min_quantity' => 1, 'unit' => 'шт', 'description' => 'Статор электродвигателя'],
            ['name' => 'Коллектор', 'price' => 550.00, 'quantity' => 8, 'min_quantity' => 2, 'unit' => 'шт', 'description' => 'Коллектор ротора'],
            ['name' => 'Крышка подшипника', 'price' => 150.00, 'quantity' => 25, 'min_quantity' => 5, 'unit' => 'шт', 'description' => 'Крышка для подшипника'],
            ['name' => 'Винт крепежный', 'price' => 25.00, 'quantity' => 100, 'min_quantity' => 20, 'unit' => 'шт', 'description' => 'Винт крепежный M3'],
            ['name' => 'Винт крепежный M4', 'price' => 30.00, 'quantity' => 80, 'min_quantity' => 20, 'unit' => 'шт', 'description' => 'Винт крепежный M4'],
            ['name' => 'Шайба пружинная', 'price' => 15.00, 'quantity' => 150, 'min_quantity' => 30, 'unit' => 'шт', 'description' => 'Шайба пружинная'],
            ['name' => 'Гайка M3', 'price' => 20.00, 'quantity' => 120, 'min_quantity' => 30, 'unit' => 'шт', 'description' => 'Гайка крепежная M3'],
            ['name' => 'Гайка M4', 'price' => 25.00, 'quantity' => 100, 'min_quantity' => 30, 'unit' => 'шт', 'description' => 'Гайка крепежная M4'],
            ['name' => 'Кольцо уплотнительное', 'price' => 45.00, 'quantity' => 40, 'min_quantity' => 10, 'unit' => 'шт', 'description' => 'Уплотнительное кольцо'],
            ['name' => 'Прокладка', 'price' => 35.00, 'quantity' => 50, 'min_quantity' => 10, 'unit' => 'шт', 'description' => 'Прокладка уплотнительная'],
        ];

        foreach ($parts as $part) {
            WarehouseItem::firstOrCreate(
                [
                    'warehouse_category_id' => $partsCategory->id,
                    'name' => $part['name'],
                ],
                [
                    'description' => $part['description'] ?? null,
                    'unit' => $part['unit'],
                    'quantity' => $part['quantity'],
                    'reserved_quantity' => 0,
                    'min_quantity' => $part['min_quantity'],
                    'price' => $part['price'],
                    'location' => 'Стеллаж А',
                    'is_active' => true,
                ]
            );
        }

        // Расходные материалы (25-30 товаров)
        $consumables = [
            ['name' => 'Масло для машинок', 'price' => 450.00, 'quantity' => 20, 'min_quantity' => 5, 'unit' => 'флакон', 'description' => 'Специальное масло для смазки машинок'],
            ['name' => 'Смазка литиевая', 'price' => 380.00, 'quantity' => 15, 'min_quantity' => 3, 'unit' => 'туба', 'description' => 'Литиевая смазка для подшипников'],
            ['name' => 'Смазка силиконовая', 'price' => 320.00, 'quantity' => 18, 'min_quantity' => 3, 'unit' => 'туба', 'description' => 'Силиконовая смазка'],
            ['name' => 'Абразивный диск 100мм', 'price' => 85.00, 'quantity' => 50, 'min_quantity' => 10, 'unit' => 'шт', 'description' => 'Абразивный диск для заточки'],
            ['name' => 'Абразивный диск 125мм', 'price' => 95.00, 'quantity' => 45, 'min_quantity' => 10, 'unit' => 'шт', 'description' => 'Абразивный диск 125мм'],
            ['name' => 'Абразивный диск 150мм', 'price' => 110.00, 'quantity' => 40, 'min_quantity' => 10, 'unit' => 'шт', 'description' => 'Абразивный диск 150мм'],
            ['name' => 'Паста для полировки', 'price' => 280.00, 'quantity' => 25, 'min_quantity' => 5, 'unit' => 'банка', 'description' => 'Паста для финальной полировки'],
            ['name' => 'Паста алмазная 1мкм', 'price' => 1200.00, 'quantity' => 8, 'min_quantity' => 2, 'unit' => 'банка', 'description' => 'Алмазная паста 1 микрон'],
            ['name' => 'Паста алмазная 3мкм', 'price' => 1100.00, 'quantity' => 10, 'min_quantity' => 2, 'unit' => 'банка', 'description' => 'Алмазная паста 3 микрона'],
            ['name' => 'Паста алмазная 6мкм', 'price' => 1050.00, 'quantity' => 10, 'min_quantity' => 2, 'unit' => 'банка', 'description' => 'Алмазная паста 6 микрон'],
            ['name' => 'Салфетки безворсовые', 'price' => 180.00, 'quantity' => 30, 'min_quantity' => 10, 'unit' => 'упаковка', 'description' => 'Салфетки для очистки'],
            ['name' => 'Салфетки микрофибра', 'price' => 220.00, 'quantity' => 25, 'min_quantity' => 10, 'unit' => 'упаковка', 'description' => 'Салфетки из микрофибры'],
            ['name' => 'Перчатки нитриловые', 'price' => 350.00, 'quantity' => 20, 'min_quantity' => 5, 'unit' => 'упаковка', 'description' => 'Перчатки нитриловые одноразовые'],
            ['name' => 'Перчатки латексные', 'price' => 320.00, 'quantity' => 20, 'min_quantity' => 5, 'unit' => 'упаковка', 'description' => 'Перчатки латексные'],
            ['name' => 'Антисептик для рук', 'price' => 280.00, 'quantity' => 15, 'min_quantity' => 3, 'unit' => 'флакон', 'description' => 'Антисептик для обработки рук'],
            ['name' => 'Спирт изопропиловый', 'price' => 450.00, 'quantity' => 12, 'min_quantity' => 3, 'unit' => 'литр', 'description' => 'Изопропиловый спирт для очистки'],
            ['name' => 'Растворитель', 'price' => 380.00, 'quantity' => 10, 'min_quantity' => 2, 'unit' => 'литр', 'description' => 'Растворитель для очистки'],
            ['name' => 'Кисть для очистки', 'price' => 120.00, 'quantity' => 20, 'min_quantity' => 5, 'unit' => 'шт', 'description' => 'Кисть для очистки механизмов'],
            ['name' => 'Ватные палочки', 'price' => 95.00, 'quantity' => 30, 'min_quantity' => 10, 'unit' => 'упаковка', 'description' => 'Ватные палочки для очистки'],
            ['name' => 'Скотч двусторонний', 'price' => 180.00, 'quantity' => 15, 'min_quantity' => 3, 'unit' => 'рулон', 'description' => 'Двусторонний скотч'],
            ['name' => 'Изолента', 'price' => 85.00, 'quantity' => 25, 'min_quantity' => 5, 'unit' => 'рулон', 'description' => 'Изоляционная лента'],
            ['name' => 'Припой оловянный', 'price' => 450.00, 'quantity' => 10, 'min_quantity' => 2, 'unit' => 'катушка', 'description' => 'Припой для пайки'],
            ['name' => 'Флюс для пайки', 'price' => 220.00, 'quantity' => 15, 'min_quantity' => 3, 'unit' => 'флакон', 'description' => 'Флюс для пайки'],
            ['name' => 'Термоусадка 3мм', 'price' => 150.00, 'quantity' => 20, 'min_quantity' => 5, 'unit' => 'метр', 'description' => 'Термоусадка 3мм'],
            ['name' => 'Термоусадка 5мм', 'price' => 180.00, 'quantity' => 18, 'min_quantity' => 5, 'unit' => 'метр', 'description' => 'Термоусадка 5мм'],
            ['name' => 'Канифоль', 'price' => 95.00, 'quantity' => 20, 'min_quantity' => 5, 'unit' => 'банка', 'description' => 'Канифоль для пайки'],
            ['name' => 'Наждачная бумага P120', 'price' => 45.00, 'quantity' => 40, 'min_quantity' => 10, 'unit' => 'лист', 'description' => 'Наждачная бумага P120'],
            ['name' => 'Наждачная бумага P240', 'price' => 50.00, 'quantity' => 40, 'min_quantity' => 10, 'unit' => 'лист', 'description' => 'Наждачная бумага P240'],
            ['name' => 'Наждачная бумага P400', 'price' => 55.00, 'quantity' => 35, 'min_quantity' => 10, 'unit' => 'лист', 'description' => 'Наждачная бумага P400'],
            ['name' => 'Наждачная бумага P800', 'price' => 60.00, 'quantity' => 35, 'min_quantity' => 10, 'unit' => 'лист', 'description' => 'Наждачная бумага P800'],
            ['name' => 'Наждачная бумага P1000', 'price' => 65.00, 'quantity' => 30, 'min_quantity' => 10, 'unit' => 'лист', 'description' => 'Наждачная бумага P1000'],
        ];

        foreach ($consumables as $consumable) {
            WarehouseItem::firstOrCreate(
                [
                    'warehouse_category_id' => $consumablesCategory->id,
                    'name' => $consumable['name'],
                ],
                [
                    'description' => $consumable['description'] ?? null,
                    'unit' => $consumable['unit'],
                    'quantity' => $consumable['quantity'],
                    'reserved_quantity' => 0,
                    'min_quantity' => $consumable['min_quantity'],
                    'price' => $consumable['price'],
                    'location' => 'Стеллаж Б',
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Склад заполнен: создано 2 категории и ' . (count($parts) + count($consumables)) . ' товаров');
    }
}
