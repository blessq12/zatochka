<?php

namespace App\Projectors;

use App\Domain\Client\Event\ClientCreated;
use App\Domain\Client\Event\ClientRegistered;
use App\Domain\Client\Event\ClientLoggedIn;
use App\Domain\Client\Entity\Client;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class ClientProjector extends Projector
{
    public function onClientCreated(ClientCreated $event): void
    {
        // Просто создаем Domain Entity - никаких БД операций
        new Client(
            id: (string) $event->clientId,
            fullName: '',
            phone: '',
            telegram: null,
            birthDate: null,
            deliveryAddress: null,
            password: null,
            isDeleted: false,
            createdAt: now(),
            updatedAt: now()
        );
    }

    public function onClientRegistered(ClientRegistered $event): void
    {
        // Создаем Domain Entity для регистрации клиента
        new Client(
            id: null,
            fullName: $event->fullName,
            phone: $event->phone,
            email: $event->email,
            telegram: $event->telegram,
            birthDate: $event->birthDate,
            deliveryAddress: $event->deliveryAddress,
            password: $event->password,
            isDeleted: false,
            createdAt: now(),
            updatedAt: now()
        );
    }

    public function onClientLoggedIn(ClientLoggedIn $event): void
    {
        // Логируем вход клиента - никаких БД операций
        // Это событие используется для аналитики и логирования
    }
}
