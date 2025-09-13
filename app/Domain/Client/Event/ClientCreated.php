<?php

namespace App\Domain\Client\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class ClientCreated extends ShouldBeStored
{
    public function __construct(
        public readonly int $clientId
    ) {}
}
