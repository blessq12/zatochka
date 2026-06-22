<?php

namespace Database\Seeders;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\CommandHandler\RegisterEquipmentHandler;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use Illuminate\Database\Seeder;

final class EquipmentSeeder extends Seeder
{
    public const STRONG_2100_NAME = 'Аппарат Strong 2100';

    public function run(): void
    {
        $items = [
            [
                'name' => self::STRONG_2100_NAME,
                'brand' => 'Strong',
                'model' => '2100',
                'serial_numbers' => [
                    'ручка' => 'SN-STR-2100-HND',
                    'блок питания' => 'SN-STR-2100-PSU',
                    'блок управления' => 'SN-STR-2100-CTRL',
                ],
            ],
            [
                'name' => 'Marathon Champion 3',
                'brand' => 'Marathon',
                'model' => 'Champion 3',
                'serial_numbers' => [
                    'ручка' => 'MC3-2024-7781',
                ],
            ],
            [
                'name' => 'Фрезер Micro NX',
                'brand' => 'Micro',
                'model' => 'NX',
                'serial_numbers' => [
                    'корпус' => 'NX-4412',
                    'педаль' => 'NX-4412-B',
                ],
            ],
        ];

        $register = app(RegisterEquipmentHandler::class);

        foreach ($items as $item) {
            if (EquipmentModel::query()->where('name', $item['name'])->exists()) {
                continue;
            }

            $register->handle(new RegisterEquipmentCommand(
                name: $item['name'],
                serialNumbers: $item['serial_numbers'],
                brand: $item['brand'],
                model: $item['model'],
            ));
        }
    }
}
