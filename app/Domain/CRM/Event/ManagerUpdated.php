<?php

namespace App\Domain\CRM\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class ManagerUpdated implements DomainEvent
{
    public function __construct(
        public EntityId $managerId,
        public string $email,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
