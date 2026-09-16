<?php

namespace App\Domain\CRM\Entity;

use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class RepairHistoryEntry
{
    public function __construct(
        public EntityId $id,
        public EntityId $orderItemId,
        public string $summary,
        public DateTimeImmutable $recordedAt = new DateTimeImmutable(),
    ) {}
}
