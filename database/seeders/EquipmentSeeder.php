<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Создает оборудование из области подологии, маникюра, груммеров
     */
    public function run(): void
    {
        // Получаем случайных клиентов для привязки оборудования
        $clients = Client::inRandomOrder()->limit(5)->get();

        $equipment = [
            // Подология
            [
                'name' => 'Аппарат для педикюра Strong 210',
                'type' => 'Аппарат для педикюра',
                'brand' => 'Strong',
                'model' => '210',
                'serial_number' => 'STR-210-' . rand(1000, 9999),
                'description' => 'Профессиональный аппарат для аппаратного педикюра',
            ],
            [
                'name' => 'Фреза алмазная цилиндрическая 3.0мм',
                'type' => 'Фреза',
                'brand' => 'Strong',
                'model' => 'FR-3.0',
                'serial_number' => 'STR-FR-' . rand(1000, 9999),
                'description' => 'Алмазная фреза для обработки ногтевой пластины',
            ],
            [
                'name' => 'Аппарат для педикюра Strong 108',
                'type' => 'Аппарат для педикюра',
                'brand' => 'Strong',
                'model' => '108',
                'serial_number' => 'STR-108-' . rand(1000, 9999),
                'description' => 'Компактный аппарат для педикюра',
            ],

            // Маникюр
            [
                'name' => 'Кусачки для кутикулы 5.0мм',
                'type' => 'Кусачки',
                'brand' => 'Staleks',
                'model' => 'Pro-5.0',
                'serial_number' => 'STK-CUT-' . rand(1000, 9999),
                'description' => 'Профессиональные кусачки для удаления кутикулы',
            ],
            [
                'name' => 'Ножницы для маникюра прямые',
                'type' => 'Ножницы',
                'brand' => 'Staleks',
                'model' => 'SC-12',
                'serial_number' => 'STK-SC-' . rand(1000, 9999),
                'description' => 'Прямые ножницы для обрезки ногтей',
            ],
            [
                'name' => 'Аппарат для маникюра Strong 108',
                'type' => 'Аппарат для маникюра',
                'brand' => 'Strong',
                'model' => '108',
                'serial_number' => 'STR-MAN-' . rand(1000, 9999),
                'description' => 'Аппарат для аппаратного маникюра',
            ],
            [
                'name' => 'Пилка алмазная конусная',
                'type' => 'Пилка',
                'brand' => 'Strong',
                'model' => 'DC-2.0',
                'serial_number' => 'STR-DC-' . rand(1000, 9999),
                'description' => 'Алмазная пилка для обработки кутикулы',
            ],
            [
                'name' => 'Кусачки для кутикулы 3.5мм',
                'type' => 'Кусачки',
                'brand' => 'Staleks',
                'model' => 'Pro-3.5',
                'serial_number' => 'STK-CUT2-' . rand(1000, 9999),
                'description' => 'Кусачки для деликатной работы с кутикулой',
            ],

            // Груммеры
            [
                'name' => 'Машинка для стрижки собак Moser 1245',
                'type' => 'Машинка для стрижки',
                'brand' => 'Moser',
                'model' => '1245',
                'serial_number' => 'MOS-1245-' . rand(1000, 9999),
                'description' => 'Профессиональная машинка для груминга собак',
            ],
            [
                'name' => 'Триммер для груминга Wahl Arco',
                'type' => 'Триммер',
                'brand' => 'Wahl',
                'model' => 'Arco',
                'serial_number' => 'WAH-ARCO-' . rand(1000, 9999),
                'description' => 'Триммер для точной стрижки',
            ],
            [
                'name' => 'Ножницы для груминга прямые 7.0',
                'type' => 'Ножницы',
                'brand' => 'Moser',
                'model' => 'SC-7.0',
                'serial_number' => 'MOS-SC-' . rand(1000, 9999),
                'description' => 'Прямые ножницы для стрижки шерсти',
            ],
            [
                'name' => 'Ножницы для груминга филировочные',
                'type' => 'Ножницы',
                'brand' => 'Moser',
                'model' => 'TH-7.0',
                'serial_number' => 'MOS-TH-' . rand(1000, 9999),
                'description' => 'Филировочные ножницы для создания объема',
            ],
            [
                'name' => 'Машинка для стрижки кошек Moser 1245',
                'type' => 'Машинка для стрижки',
                'brand' => 'Moser',
                'model' => '1245-CAT',
                'serial_number' => 'MOS-CAT-' . rand(1000, 9999),
                'description' => 'Машинка для стрижки кошек',
            ],
        ];

        foreach ($equipment as $index => $equipmentData) {
            Equipment::create(array_merge($equipmentData, [
                'client_id' => $clients->random()->id ?? null,
                'is_active' => true,
                'is_deleted' => false,
            ]));
        }

        $this->command->info('Создано ' . count($equipment) . ' единиц оборудования');
    }
}
