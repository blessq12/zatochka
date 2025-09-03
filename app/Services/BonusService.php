<?php

namespace App\Services;

use App\Models\BonusSettings;
use App\Models\BonusTransaction;
use App\Models\Client;
use App\Models\Order;
use Carbon\Carbon;

class BonusService
{
    private BonusSettings $settings;

    public function __construct()
    {
        $this->settings = BonusSettings::getSettings();
    }

    /**
     * Начислить бонусы за заказ
     */
    public function earnBonusForOrder(Order $order): ?BonusTransaction
    {
        // Проверяем минимальную сумму заказа
        if ($order->final_price < $this->settings->min_order_amount) {
            return null;
        }

        // Рассчитываем бонусы
        $bonusAmount = $this->settings->calculateOrderBonus($order->final_price);

        if ($bonusAmount <= 0) {
            return null;
        }

        // Создаем транзакцию
        return BonusTransaction::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'type' => 'earn',
            'amount' => $bonusAmount,
            'description' => "Начисление бонусов за заказ {$order->order_number}",
        ]);
    }

    /**
     * Начислить бонус за первый заказ
     */
    public function earnFirstOrderBonus(Order $order): ?BonusTransaction
    {
        // Проверяем, что это первый заказ клиента
        $orderCount = Order::where('client_id', $order->client_id)
            ->where('id', '!=', $order->id)
            ->count();

        if ($orderCount > 0 || $this->settings->first_order_bonus <= 0) {
            return null;
        }

        return BonusTransaction::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'type' => 'earn',
            'amount' => $this->settings->first_order_bonus,
            'description' => "Бонус за первый заказ {$order->order_number}",
        ]);
    }

    /**
     * Начислить бонус на день рождения
     */
    public function earnBirthdayBonus(Client $client): ?BonusTransaction
    {
        if ($this->settings->birthday_bonus <= 0) {
            return null;
        }

        // Проверяем, что сегодня день рождения
        if (!$client->birth_date || !$client->birth_date->isBirthday()) {
            return null;
        }

        // Проверяем, что бонус еще не начислялся в этом году
        $existingBonus = BonusTransaction::where('client_id', $client->id)
            ->where('type', 'earn')
            ->where('description', 'like', '%день рождения%')
            ->whereYear('created_at', now()->year)
            ->first();

        if ($existingBonus) {
            return null;
        }

        return BonusTransaction::create([
            'client_id' => $client->id,
            'order_id' => null,
            'type' => 'earn',
            'amount' => $this->settings->birthday_bonus,
            'description' => "Бонус на день рождения",
        ]);
    }

    /**
     * Списать бонусы за заказ
     */
    public function spendBonusForOrder(Order $order, int $bonusAmount): ?BonusTransaction
    {
        // Проверяем минимальную сумму для списания
        if ($order->final_price < $this->settings->min_order_sum_for_spending) {
            return null;
        }

        // Проверяем баланс клиента
        $clientBalance = $this->getClientBalance($order->client_id);
        if ($clientBalance < $bonusAmount) {
            return null;
        }

        return BonusTransaction::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'type' => 'spend',
            'amount' => $bonusAmount,
            'description' => "Списание бонусов за заказ {$order->order_number}",
        ]);
    }

    /**
     * Получить баланс бонусов клиента
     */
    public function getClientBalance(int $clientId): int
    {
        $earned = BonusTransaction::where('client_id', $clientId)
            ->where('type', 'earn')
            ->sum('amount');

        $spent = BonusTransaction::where('client_id', $clientId)
            ->where('type', 'spend')
            ->sum('amount');

        return $earned - $spent;
    }

    /**
     * Получить активный баланс (с учетом срока действия)
     */
    public function getActiveClientBalance(int $clientId): int
    {
        $expireDate = now()->subDays($this->settings->expire_days);

        $earned = BonusTransaction::where('client_id', $clientId)
            ->where('type', 'earn')
            ->where('created_at', '>=', $expireDate)
            ->sum('amount');

        $spent = BonusTransaction::where('client_id', $clientId)
            ->where('type', 'spend')
            ->where('created_at', '>=', $expireDate)
            ->sum('amount');

        return $earned - $spent;
    }

    /**
     * Конвертировать бонусы в рубли
     */
    public function convertBonusToRubles(int $bonusAmount): float
    {
        return $this->settings->convertBonusToRubles($bonusAmount);
    }

    /**
     * Конвертировать рубли в бонусы
     */
    public function convertRublesToBonus(float $rublesAmount): int
    {
        return $this->settings->convertRublesToBonus($rublesAmount);
    }

    /**
     * Очистить просроченные бонусы (команда для cron)
     */
    public function clearExpiredBonuses(): int
    {
        $expireDate = now()->subDays($this->settings->expire_days);

        // Находим просроченные начисления
        $expiredEarnings = BonusTransaction::where('type', 'earn')
            ->where('created_at', '<', $expireDate)
            ->get();

        $clearedCount = 0;

        foreach ($expiredEarnings as $earning) {
            $clientBalance = $this->getClientBalance($earning->client_id);

            // Если у клиента есть активные бонусы, списываем просроченные
            if ($clientBalance > 0) {
                $spendAmount = min($earning->amount, $clientBalance);

                if ($spendAmount > 0) {
                    BonusTransaction::create([
                        'client_id' => $earning->client_id,
                        'order_id' => null,
                        'type' => 'spend',
                        'amount' => $spendAmount,
                        'description' => "Списание просроченных бонусов",
                    ]);

                    $clearedCount += $spendAmount;
                }
            }
        }

        return $clearedCount;
    }
}
