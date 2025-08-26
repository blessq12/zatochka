<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientBonus;
use App\Models\BonusTransaction;
use App\Models\BonusSetting;
use App\Models\Order;
use App\Events\Bonus\BonusEarned;
use App\Events\Bonus\BonusSpent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BonusService
{
    /**
     * Начислить бонусы клиенту за заказ
     */
    public function awardBonusForOrder(Order $order): void
    {
        if (!$order->canEarnBonus()) {
            return;
        }

        $client = $order->client;
        $bonusAmount = $order->calculateBonusEarnAmount();

        DB::transaction(function () use ($client, $bonusAmount, $order) {
            $clientBonus = $this->getOrCreateClientBonus($client);

            // Начисляем бонусы
            $clientBonus->increment('balance', $bonusAmount);
            $clientBonus->increment('total_earned', $bonusAmount);
            $clientBonus->updateExpiration();

            // Создаем транзакцию
            BonusTransaction::createEarn(
                $client->id,
                $bonusAmount,
                "Начисление бонусов за заказ {$order->order_number}",
                $order->id
            );
        });

        // Отправляем событие
        event(new BonusEarned($client, $bonusAmount, 'order', $order));
    }

    /**
     * Списать бонусы клиента при оплате заказа
     */
    public function spendBonusForOrder(Order $order, float $bonusAmount): bool
    {
        $client = $order->client;
        $clientBonus = $this->getOrCreateClientBonus($client);

        // Проверяем возможность списания
        if (!$this->canSpendBonus($order, $bonusAmount, $clientBonus)) {
            return false;
        }

        DB::transaction(function () use ($clientBonus, $bonusAmount, $order, $client) {
            // Списываем бонусы
            $clientBonus->decrement('balance', $bonusAmount);
            $clientBonus->increment('total_spent', $bonusAmount);
            $clientBonus->updateExpiration();

            // Создаем транзакцию
            BonusTransaction::createSpend(
                $client->id,
                $bonusAmount,
                "Списание бонусов за заказ {$order->order_number}",
                $order->id
            );
        });

        // Отправляем событие
        event(new BonusSpent($client, $bonusAmount, 'order', $order));

        return true;
    }

    /**
     * Начислить бонусы за день рождения
     */
    public function awardBirthdayBonus(Client $client): void
    {
        $bonusAmount = BonusSetting::getFloat('birthday_bonus_amount', 1000);

        DB::transaction(function () use ($client, $bonusAmount) {
            $clientBonus = $this->getOrCreateClientBonus($client);

            $clientBonus->increment('balance', $bonusAmount);
            $clientBonus->increment('total_earned', $bonusAmount);
            $clientBonus->updateExpiration();

            BonusTransaction::createEarn(
                $client->id,
                $bonusAmount,
                'Бонус за день рождения'
            );
        });

        // Отправляем событие
        event(new BonusEarned($client, $bonusAmount, 'birthday'));
    }

    /**
     * Начислить бонусы за первый отзыв
     */
    public function awardFirstReviewBonus(Client $client): void
    {
        // Проверяем, что это действительно первый отзыв
        if ($client->reviews()->count() > 1) {
            return;
        }

        // Проверяем, что бонус за первый отзыв еще не был начислен
        $existingTransaction = BonusTransaction::where('client_id', $client->id)
            ->where('description', 'Бонус за первый отзыв')
            ->exists();

        if ($existingTransaction) {
            return;
        }

        $bonusAmount = BonusSetting::getFloat('first_review_bonus_amount', 1000);

        DB::transaction(function () use ($client, $bonusAmount) {
            $clientBonus = $this->getOrCreateClientBonus($client);

            $clientBonus->increment('balance', $bonusAmount);
            $clientBonus->increment('total_earned', $bonusAmount);
            $clientBonus->updateExpiration();

            BonusTransaction::createEarn(
                $client->id,
                $bonusAmount,
                'Бонус за первый отзыв'
            );
        });

        // Отправляем событие
        event(new BonusEarned($client, $bonusAmount, 'review'));
    }

    /**
     * Рассчитать максимальную сумму бонусов для списания
     */
    public function calculateMaxBonusSpend(Order $order): float
    {
        $client = $order->client;
        $clientBonus = $this->getOrCreateClientBonus($client);
        $availableBalance = $clientBonus->getAvailableBalance();

        $maxPercent = BonusSetting::getFloat('max_bonus_spend_percent', 50);
        $maxByPercent = $order->final_price * ($maxPercent / 100);

        return min($availableBalance, $maxByPercent);
    }

    /**
     * Проверить возможность списания бонусов
     */
    public function canSpendBonus(Order $order, float $bonusAmount, ?ClientBonus $clientBonus = null): bool
    {
        $minOrderAmount = BonusSetting::getFloat('min_order_amount_for_spend', 3000);

        if ($order->final_price < $minOrderAmount) {
            return false;
        }

        if (!$clientBonus) {
            $clientBonus = $this->getOrCreateClientBonus($order->client);
        }

        if (!$clientBonus->canSpend($bonusAmount)) {
            return false;
        }

        $maxSpend = $this->calculateMaxBonusSpend($order);

        return $bonusAmount <= $maxSpend;
    }

    /**
     * Получить или создать запись бонусов клиента
     */
    public function getOrCreateClientBonus(Client $client): ClientBonus
    {
        $clientBonus = $client->bonus;

        if (!$clientBonus) {
            $clientBonus = $client->bonus()->create([
                'balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0,
                'expires_at' => now()->addMonths(3),
            ]);

            // Обновляем связь в модели клиента
            $client->setRelation('bonus', $clientBonus);
        }

        return $clientBonus;
    }

    /**
     * Списать просроченные бонусы
     */
    public function expireOldBonuses(): int
    {
        $expiredBonuses = ClientBonus::where('expires_at', '<', now())
            ->where('balance', '>', 0)
            ->get();

        $totalExpired = 0;

        foreach ($expiredBonuses as $bonus) {
            $expiredAmount = $bonus->balance;

            DB::transaction(function () use ($bonus, $expiredAmount) {
                $bonus->update(['balance' => 0]);

                BonusTransaction::createSpend(
                    $bonus->client_id,
                    $expiredAmount,
                    'Списание просроченных бонусов'
                );

                // Отправляем событие
                event(new BonusSpent($bonus->client, $expiredAmount, 'expired'));
            });

            $totalExpired += $expiredAmount;
        }

        return $totalExpired;
    }
}
