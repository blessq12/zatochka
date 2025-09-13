<?php

namespace App\Reactors;

use App\Domain\Bonus\Event\BonusAccountCreated;
use App\Domain\Bonus\Event\BonusTransactionCreated;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class BonusReactor extends Reactor
{
    public function onBonusAccountCreated(BonusAccountCreated $event): void
    {
        // Отправка уведомления о создании бонусного счета
        // Логирование в Activity Log
    }

    public function onBonusTransactionCreated(BonusTransactionCreated $event): void
    {
        // Обновление баланса бонусного счета
        // Отправка уведомления о начислении/списании бонусов
        // Проверка лимитов и правил
    }
}
