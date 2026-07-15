<?php

namespace App\Domain\Finance\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final readonly class CashOperationRegistered implements DomainEvent
{
    public function __construct(
        public EntityId $cashOperationId,
        public Money $amount,
        public string $type,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
