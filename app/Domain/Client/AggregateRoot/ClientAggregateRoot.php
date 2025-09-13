<?php

namespace App\Domain\Client\AggregateRoot;

use App\Domain\Client\Event\ClientCreated;
use App\Domain\Client\Event\ClientUpdated;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class ClientAggregateRoot extends AggregateRoot
{
    public function createClient(string $clientId, string $phone, string $fullName, array $clientData): self
    {
        $this->recordThat(new ClientCreated(
            clientId: $clientId,
            phone: $phone,
            fullName: $fullName,
            clientData: $clientData
        ));

        return $this;
    }

    public function updateClient(string $clientId, string $phone, string $fullName, array $clientData): self
    {
        $this->recordThat(new ClientUpdated(
            clientId: $clientId,
            phone: $phone,
            fullName: $fullName,
            clientData: $clientData
        ));

        return $this;
    }
}
