<?php

namespace App\Domain\Delivery\Entity;

use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class CourierAssignment
{
    public function __construct(
        public EntityId $courierId,
        public DateTimeImmutable $assignedAt = new DateTimeImmutable(),
    ) {}
}
