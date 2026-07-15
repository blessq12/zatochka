<?php

namespace App\Domain\Workshop\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class DiagnosisCompleted implements DomainEvent
{
    public function __construct(
        public EntityId $productionTaskId,
        public EntityId $orderItemId,
        public EntityId $diagnosisId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
