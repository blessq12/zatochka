<?php

namespace Database\Seeders;

use App\Models\Types\DeliveryType;
use Illuminate\Database\Seeder;

class DeliveryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deliveryTypes = [
            [
                'name' => 'Самовывоз',
                'slug' => 'pickup',
                'description' => 'Самовывоз из офиса',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Курьерская доставка',
                'slug' => 'courier',
                'description' => 'Доставка курьером',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Почта России',
                'slug' => 'russian_post',
                'description' => 'Доставка почтой России',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'СДЭК',
                'slug' => 'cdek',
                'description' => 'Доставка СДЭК',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($deliveryTypes as $deliveryType) {
            DeliveryType::firstOrCreate(
                ['slug' => $deliveryType['slug']],
                $deliveryType
            );
        }
    }
}
