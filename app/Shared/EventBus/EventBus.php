<?php

namespace App\Shared\EventBus;

interface EventBus
{
    public function publish(object $event): void;

    /**
     * @param  class-string  $event
     * @param  class-string  $listener
     */
    public function listen(string $event, string $listener): void;
}
