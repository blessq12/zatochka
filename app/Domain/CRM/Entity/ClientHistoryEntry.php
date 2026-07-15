<?php

namespace App\Domain\CRM\Entity;

use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class ClientHistoryEntry
{
    public function __construct(
        public EntityId $id,
        public EntityId $orderId,
        public string $note,
        public DateTimeImmutable $recordedAt = new DateTimeImmutable(),
    ) {}
}
