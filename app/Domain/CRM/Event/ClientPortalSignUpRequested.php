<?php

namespace App\Domain\CRM\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

/** Fired when portal signup needs Identity ClientAccount provisioning. */
final readonly class ClientPortalSignUpRequested implements DomainEvent
{
    public function __construct(
        public EntityId $clientId,
        public string $phone,
        public string $passwordHash,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
