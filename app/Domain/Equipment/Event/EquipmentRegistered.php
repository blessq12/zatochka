<?php

namespace App\Domain\Equipment\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class EquipmentRegistered implements DomainEvent
{
    public function __construct(
        public EntityId $equipmentId,
        public string $title,
        public ?EntityId $clientId = null,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
