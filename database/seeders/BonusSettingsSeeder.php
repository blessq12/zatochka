<?php

namespace Database\Seeders;

use App\Models\BonusSettings;
use Illuminate\Database\Seeder;

class BonusSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем настройки бонусной системы с разумными значениями по умолчанию
        BonusSettings::firstOrCreate([], [
            'birthday_bonus' => 100, // 100 бонусов на день рождения
            'first_order_bonus' => 50, // 50 бонусов за первый заказ
            'rate' => 1.00, // 1 рубль = 1 бонус
            'percent_per_order' => 5.00, // 5% от суммы заказа в бонусы
            'min_order_sum_for_spending' => 1000.00, // Минимум 1000₽ для списания бонусов
            'expire_days' => 365, // Бонусы действуют 1 год
            'min_order_amount' => 100.00, // Минимум 100₽ для начисления бонусов
            'max_bonus_per_order' => 1000, // Максимум 1000 бонусов за заказ
        ]);

        $this->command->info('Настройки бонусной системы созданы успешно!');
    }
}
