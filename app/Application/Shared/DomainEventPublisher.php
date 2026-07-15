<?php

namespace App\Application\Shared;

use App\Shared\Domain\DomainEvent;

interface DomainEventPublisher
{
    /** @param list<DomainEvent> $events */
    public function publish(array $events): void;
}
