<?php

namespace App\Infrastructure\Shared\EventBus;

use App\Shared\EventBus\EventBus;
use Illuminate\Support\Facades\Event;

final class LaravelEventBus implements EventBus
{
    public function publish(object $event): void
    {
        Event::dispatch($event);
    }

    public function listen(string $event, string $listener): void
    {
        Event::listen($event, $listener);
    }
}
