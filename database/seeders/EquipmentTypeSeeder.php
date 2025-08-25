<?php

namespace Database\Seeders;

use App\Models\Types\EquipmentType;
use Illuminate\Database\Seeder;

class EquipmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipmentTypes = [
            [
                'name' => 'Маникюрные инструменты',
                'slug' => 'manicure',
                'description' => 'Инструменты для маникюра и педикюра',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Парикмахерские инструменты',
                'slug' => 'hairdresser',
                'description' => 'Инструменты для парикмахеров',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Инструменты для бровей и ресниц',
                'slug' => 'lash_brow',
                'description' => 'Инструменты для работы с бровями и ресницами',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Подологические инструменты',
                'slug' => 'podology',
                'description' => 'Инструменты для подологии',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Электроинструменты',
                'slug' => 'electric',
                'description' => 'Электрические инструменты',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($equipmentTypes as $equipmentType) {
            EquipmentType::create($equipmentType);
        }
    }
}
