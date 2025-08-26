<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BonusSetting;

class BonusSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $config = [
            'bonus_percent_per_order' => 5,
            'bonus_exchange_rate' => 1,
            'bonus_expiration_months' => 3,
            'birthday_bonus_amount' => 1000,
            'min_order_amount_for_bonus' => 1500,
            'min_order_amount_for_spend' => 3000,
            'max_bonus_spend_percent' => 50,
            'first_review_bonus_amount' => 1000,
        ];

        BonusSetting::updateOrCreate(
            ['key' => 'bonus_config'],
            [
                'value' => $config,
                'description' => 'Основная конфигурация бонусной системы',
            ]
        );
    }
}
