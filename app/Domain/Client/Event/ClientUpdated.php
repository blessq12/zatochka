<?php

namespace App\Domain\Client\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class ClientUpdated extends ShouldBeStored
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $phone,
        public readonly string $fullName,
        public readonly array $clientData
    ) {}
}
