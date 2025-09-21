<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Repair;
use App\Models\User;
use Illuminate\Database\Seeder;

class RepairSeeder extends Seeder
{
    public function run(): void
    {
        // Получаем заказы для создания ремонтов
        $orders = Order::take(5)->get();
        $masters = User::where('id', '>', 1)->take(3)->get();

        if ($orders->isEmpty()) {
            $this->command->info('Нет заказов для создания ремонтов');

            return;
        }

        if ($masters->isEmpty()) {
            $this->command->info('Нет мастеров для назначения ремонтов');

            return;
        }

        $repairs = [
            [
                'order_id' => $orders[0]->id,
                'description' => 'Заточка кухонных ножей. Клиент принес 3 ножа разного размера. Ножи затупились от длительного использования. Требуется восстановление режущей кромки. Заточка на точильном станке, полировка режущей кромки. Клиент просил сделать острее обычного.',
                'used_materials' => json_encode([
                    'abrasive_stones' => 2,
                    'polishing_compound' => 1,
                    'honing_oil' => 1,
                ]),
                'work_time_minutes' => 120,
                'price' => 1500.00,
            ],
            [
                'order_id' => $orders[1]->id ?? $orders[0]->id,
                'description' => 'Ремонт ножниц. Не режут бумагу, тупые лезвия. Лезвия ножниц затупились и имеют зазубрины. Требуется заточка и выравнивание. Клиентка принесла дорогие ножницы, просит аккуратно.',
                'used_materials' => json_encode([
                    'fine_abrasive' => 1,
                    'polishing_paste' => 1,
                ]),
                'work_time_minutes' => 60,
                'price' => 800.00,
            ],
            [
                'order_id' => $orders[2]->id ?? $orders[0]->id,
                'description' => 'Заточка садовых инструментов. Секатор и сучкорез. Инструменты затупились от работы с толстыми ветками. Требуется восстановление режущих кромок. Заточка режущих кромок, закалка, полировка. Работа выполнена качественно, клиент доволен.',
                'used_materials' => json_encode([
                    'grinding_wheel' => 1,
                    'honing_oil' => 1,
                    'heat_treatment' => 1,
                ]),
                'work_time_minutes' => 180,
                'price' => 2200.00,
            ],
            [
                'order_id' => $orders[3]->id ?? $orders[0]->id,
                'description' => 'Ремонт электрического точильного станка. Не включается. Проблема с электродвигателем. Требуется замена щеток и проверка обмотки. Диагностика двигателя, замена щеток. Ждем поставку новых щеток от поставщика.',
                'used_materials' => json_encode([
                    'motor_brushes' => 2,
                    'electrical_tape' => 1,
                ]),
                'work_time_minutes' => 240,
                'price' => 3500.00,
            ],
            [
                'order_id' => $orders[4]->id ?? $orders[0]->id,
                'description' => 'Заточка профессиональных поварских ножей. Набор из 5 ножей. Срочный заказ от ресторана. Нужно сделать быстро и качественно. Восстановление режущих кромок всех ножей, полировка до зеркального блеска.',
                'used_materials' => json_encode([
                    'professional_stones' => 3,
                    'polishing_compound' => 2,
                    'honing_steel' => 1,
                ]),
                'work_time_minutes' => 300,
                'price' => 4500.00,
            ],
        ];

        foreach ($repairs as $repairData) {
            Repair::create($repairData);
        }

        $this->command->info('Создано '.count($repairs).' демо ремонтов');
    }
}
