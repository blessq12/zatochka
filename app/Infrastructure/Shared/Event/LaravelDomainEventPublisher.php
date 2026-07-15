<?php

namespace App\Infrastructure\Shared\Event;

use App\Application\Shared\DomainEventPublisher;
use App\Shared\Domain\DomainEvent;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class LaravelDomainEventPublisher implements DomainEventPublisher
{
    public function __construct(
        private Dispatcher $dispatcher,
    ) {}

    public function publish(array $events): void
    {
        foreach ($events as $event) {
            if (! $event instanceof DomainEvent) {
                continue;
            }

            $this->dispatcher->dispatch($event);
        }
    }
}
