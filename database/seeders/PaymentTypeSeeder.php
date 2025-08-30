<?php

namespace Database\Seeders;

use App\Models\Types\PaymentType;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentTypes = [
            [
                'name' => 'Наличные',
                'slug' => 'cash',
                'description' => 'Оплата наличными при получении',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Банковская карта',
                'slug' => 'card',
                'description' => 'Оплата банковской картой',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Безналичный расчет',
                'slug' => 'bank_transfer',
                'description' => 'Безналичный расчет для юридических лиц',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'СБП',
                'slug' => 'sbp',
                'description' => 'Система быстрых платежей',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($paymentTypes as $paymentType) {
            PaymentType::firstOrCreate(
                ['slug' => $paymentType['slug']],
                $paymentType
            );
        }
    }
}
