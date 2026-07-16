<?php

namespace App\Domain\Workshop\VO;

use App\Shared\ValueObject\EntityId;

/**
 * Target of a performed work: always an order item; for repair also an equipment component.
 */
final readonly class WorkTarget
{
    public function __construct(
        public EntityId $orderItemId,
        public ?EntityId $equipmentComponentId = null,
    ) {}
}
