<?php

namespace Database\Seeders;

use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use Illuminate\Database\Seeder;

final class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Аппарат Strong 2100',
                'brand' => 'Strong',
                'model' => '2100',
                'serial_numbers' => ['SN-STR-2100-001'],
            ],
            [
                'name' => 'Marathon Champion 3',
                'brand' => 'Marathon',
                'model' => 'Champion 3',
                'serial_numbers' => ['MC3-2024-7781'],
            ],
            [
                'name' => 'Фрезер Micro NX',
                'brand' => 'Micro',
                'model' => 'NX',
                'serial_numbers' => ['NX-4412', 'NX-4412-B'],
            ],
        ];

        foreach ($items as $item) {
            EquipmentModel::query()->firstOrCreate(
                ['name' => $item['name']],
                [
                    'brand' => $item['brand'],
                    'model' => $item['model'],
                    'serial_numbers' => $item['serial_numbers'],
                ],
            );
        }
    }
}
