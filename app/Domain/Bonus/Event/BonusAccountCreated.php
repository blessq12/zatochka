<?php

namespace App\Domain\Bonus\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class BonusAccountCreated extends ShouldBeStored
{
    public function __construct(
        public readonly int $clientId,
        public readonly int $initialBalance
    ) {}
}
