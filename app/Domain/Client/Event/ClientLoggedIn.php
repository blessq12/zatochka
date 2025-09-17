<?php

namespace App\Domain\Client\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class ClientLoggedIn extends ShouldBeStored
{
    public function __construct(
        public readonly string $phone
    ) {}
}
