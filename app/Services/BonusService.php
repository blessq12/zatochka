<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use Carbon\Carbon;

class BonusService
{
    /**
     * Начислить бонусы клиенту за заказ
     */
    public function awardBonusForOrder(Order $order): void
    {
        $client = $order->client;
        $bonusAmount = $this->calculateBonusAmount($order->total_amount);

        // Здесь можно добавить логику начисления бонусов
        // Например, сохранить в отдельной таблице бонусов

        // Отправляем уведомление о начислении бонусов
        $this->sendBonusNotification($client, $bonusAmount, $order);
    }

    /**
     * Рассчитать сумму бонусов за заказ
     */
    protected function calculateBonusAmount(float $orderAmount): float
    {
        // 5% от суммы заказа
        return $orderAmount * 0.05;
    }

    /**
     * Отправить уведомление о начислении бонусов
     */
    protected function sendBonusNotification(Client $client, float $bonusAmount, Order $order): void
    {
        $message = "🎁 Здравствуйте, {$client->full_name}!\n\n";
        $message .= "Спасибо за заказ {$order->order_number}!\n";
        $message .= "Вам начислено " . number_format($bonusAmount, 0) . " бонусных рублей.\n\n";
        $message .= "Бонусы можно использовать при следующем заказе.\n";
        $message .= "С уважением, команда Заточка";

        // Сохраняем уведомление
        $client->notifications()->create([
            'type' => 'bonus',
            'message_text' => $message,
            'sent_at' => now(),
        ]);
    }

    /**
     * Проверить и начислить бонусы за день рождения
     */
    public function awardBirthdayBonus(Client $client): void
    {
        $bonusAmount = 500; // Фиксированный бонус на ДР

        $message = "🎂 Дорогой {$client->full_name}!\n\n";
        $message .= "В честь Вашего дня рождения начисляем Вам {$bonusAmount} бонусных рублей!\n";
        $message .= "Используйте их при следующем заказе.\n\n";
        $message .= "С уважением, команда Заточка";

        // Сохраняем уведомление
        $client->notifications()->create([
            'type' => 'birthday_bonus',
            'message_text' => $message,
            'sent_at' => now(),
        ]);
    }

    /**
     * Начислить бонусы постоянным клиентам
     */
    public function awardLoyaltyBonus(Client $client): void
    {
        $ordersThisMonth = $client->orders()
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();

        if ($ordersThisMonth >= 3) {
            $bonusAmount = 1000; // Бонус за 3+ заказа в месяц

            $message = "👑 Здравствуйте, {$client->full_name}!\n\n";
            $message .= "Вы наш постоянный клиент! В этом месяце Вы сделали {$ordersThisMonth} заказов.\n";
            $message .= "Вам начислен бонус {$bonusAmount} рублей за лояльность!\n\n";
            $message .= "Спасибо за доверие!\n";
            $message .= "Команда Заточка";

            // Сохраняем уведомление
            $client->notifications()->create([
                'type' => 'loyalty_bonus',
                'message_text' => $message,
                'sent_at' => now(),
            ]);
        }
    }
}
