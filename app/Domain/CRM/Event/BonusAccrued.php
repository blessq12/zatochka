<?php

namespace App\Domain\CRM\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class BonusAccrued implements DomainEvent
{
    public function __construct(
        public EntityId $clientId,
        public EntityId $bonusAccountId,
        public string $amount,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
