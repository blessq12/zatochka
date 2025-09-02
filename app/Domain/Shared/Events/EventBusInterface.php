<?php

namespace App\Domain\Shared\Events;

interface EventBusInterface
{
    public function publish(object $event): void;
}
