<?php

namespace App\Domain\CRM\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

/** Fired when a CRM Manager needs an Identity ManagerAccount. */
final readonly class ManagerAccountProvisioningRequested implements DomainEvent
{
    public function __construct(
        public EntityId $managerId,
        public string $email,
        public string $passwordHash,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
