<?php

namespace App\Domain\Equipment\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class ComponentAdded implements DomainEvent
{
    public function __construct(
        public EntityId $equipmentId,
        public EntityId $componentId,
        public string $componentName,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
