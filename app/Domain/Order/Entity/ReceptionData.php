<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\VO\ReceptionCondition;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final class ReceptionData
{
    /** @param list<string> $attachmentRefs */
    public function __construct(
        private readonly EntityId $id,
        private readonly ReceptionCondition $condition,
        private readonly DateTimeImmutable $receivedAt,
        private readonly array $attachmentRefs = [],
    ) {}

    public function id(): EntityId
    {
        return $this->id;
    }

    public function condition(): ReceptionCondition
    {
        return $this->condition;
    }

    public function receivedAt(): DateTimeImmutable
    {
        return $this->receivedAt;
    }

    /** @return list<string> */
    public function attachmentRefs(): array
    {
        return $this->attachmentRefs;
    }
}
