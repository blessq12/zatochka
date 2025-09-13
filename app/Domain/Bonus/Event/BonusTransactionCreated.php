<?php

namespace App\Domain\Bonus\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class BonusTransactionCreated extends ShouldBeStored
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $accountId,
        public readonly string $type,
        public readonly int $amount,
        public readonly string $description,
        public readonly ?string $orderId = null
    ) {}
}
