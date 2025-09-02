<?php

namespace App\Infrastructure\Events;

use App\Domain\Shared\Events\EventBusInterface;
use Illuminate\Contracts\Events\Dispatcher;

class EventBus implements EventBusInterface
{
    public function __construct(private readonly Dispatcher $dispatcher) {}

    public function publish(object $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
