<?php

namespace App\Reactors;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Event\OrderStatusChanged;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class OrderReactor extends Reactor
{
    public function onOrderCreated(OrderCreated $event): void
    {
        // Отправка уведомлений о создании заказа
        // Создание бонусного счета для клиента
        // Логирование в Activity Log
    }

    public function onOrderStatusChanged(OrderStatusChanged $event): void
    {
        // Отправка уведомлений об изменении статуса
        // Начисление бонусов при завершении заказа
        // Интеграция с внешними системами
    }
}
