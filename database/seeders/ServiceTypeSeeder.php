<?php

namespace Database\Seeders;

use App\Models\Types\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceTypes = [
            [
                'name' => 'Заточка',
                'slug' => 'sharpening',
                'description' => 'Заточка инструментов',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Ремонт',
                'slug' => 'repair',
                'description' => 'Ремонт оборудования',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Обслуживание',
                'slug' => 'maintenance',
                'description' => 'Техническое обслуживание',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Консультация',
                'slug' => 'consultation',
                'description' => 'Консультационные услуги',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Прочее',
                'slug' => 'other',
                'description' => 'Другие услуги',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::create($serviceType);
        }
    }
}
