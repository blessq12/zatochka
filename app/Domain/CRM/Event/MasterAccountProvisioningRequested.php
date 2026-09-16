<?php

namespace App\Domain\CRM\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

/** Fired when a CRM Master needs an Identity MasterAccount. */
final readonly class MasterAccountProvisioningRequested implements DomainEvent
{
    public function __construct(
        public EntityId $masterId,
        public string $email,
        public string $passwordHash,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
