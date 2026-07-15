<?php

namespace App\Domain\Inventory\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class MaterialWrittenOff implements DomainEvent
{
    public function __construct(
        public EntityId $stockItemId,
        public EntityId $materialId,
        public string $quantity,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
