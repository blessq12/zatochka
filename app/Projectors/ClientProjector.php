<?php

namespace App\Projectors;

use App\Domain\Client\Event\ClientCreated;
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
            isDeleted: false,
            createdAt: now(),
            updatedAt: now()
        );
    }
}
