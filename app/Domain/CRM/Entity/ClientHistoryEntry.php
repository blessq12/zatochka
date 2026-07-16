<?php

namespace App\Domain\CRM\Entity;

use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class ClientHistoryEntry
{
    public function __construct(
        public EntityId $id,
        public OrderId $orderId,
        public string $note,
        public DateTimeImmutable $recordedAt = new DateTimeImmutable(),
    ) {}
}
