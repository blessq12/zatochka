<?php

namespace App\Reactors;

use App\Domain\Client\Event\ClientCreated;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class ClientReactor extends Reactor
{
    public function onClientCreated(ClientCreated $event): void
    {
        // Автоматическое создание бонусного счета
        // Отправка приветственного сообщения
        // Регистрация в Telegram боте
    }
}
