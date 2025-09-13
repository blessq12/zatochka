<?php

namespace App\Projectors;

use App\Domain\Client\Event\ClientCreated;
use App\Models\Client;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class ClientProjector extends Projector
{
    public function onClientCreated(ClientCreated $event): void
    {
        Client::create([
            'id' => $event->clientId,
            'phone' => $event->phone,
            'full_name' => $event->fullName,
            ...$event->clientData
        ]);
    }
}
