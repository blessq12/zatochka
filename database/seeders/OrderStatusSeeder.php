<?php

namespace Database\Seeders;

use App\Models\Types\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderStatuses = [
            [
                'name' => 'Новый',
                'slug' => 'new',
                'description' => 'Заказ создан, ожидает обработки',
                'color' => 'gray',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Подтвержден',
                'slug' => 'confirmed',
                'description' => 'Заказ подтвержден',
                'color' => 'info',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Передан курьеру (забор)',
                'slug' => 'courier_pickup',
                'description' => 'Заказ передан курьеру для забора',
                'color' => 'warning',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Передан мастеру',
                'slug' => 'master_received',
                'description' => 'Заказ передан мастеру',
                'color' => 'warning',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'В работе',
                'slug' => 'in_progress',
                'description' => 'Заказ в работе',
                'color' => 'warning',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Работа завершена',
                'slug' => 'work_completed',
                'description' => 'Работа по заказу завершена',
                'color' => 'success',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Передан курьеру (доставка)',
                'slug' => 'courier_delivery',
                'description' => 'Заказ передан курьеру для доставки',
                'color' => 'warning',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Готов к выдаче',
                'slug' => 'ready_for_pickup',
                'description' => 'Заказ готов к выдаче',
                'color' => 'success',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Доставлен',
                'slug' => 'delivered',
                'description' => 'Заказ доставлен клиенту',
                'color' => 'info',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Оплачен',
                'slug' => 'payment_received',
                'description' => 'Заказ оплачен',
                'color' => 'success',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Закрыт',
                'slug' => 'closed',
                'description' => 'Заказ закрыт',
                'color' => 'success',
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Отменен',
                'slug' => 'cancelled',
                'description' => 'Заказ отменен',
                'color' => 'danger',
                'is_active' => true,
                'sort_order' => 12,
            ],
        ];

        foreach ($orderStatuses as $orderStatus) {
            OrderStatus::create($orderStatus);
        }
    }
}
