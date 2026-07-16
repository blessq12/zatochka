<?php

namespace App\Application\Workshop\WorkAttachment;

use App\Domain\Order\Entity\Order;
use App\Domain\Workshop\VO\WorkTarget;

interface WorkAttachmentStrategy
{
    /**
     * Resolve and validate where a performed work must be attached for this order type.
     */
    public function resolveTarget(
        Order $order,
        ?int $orderItemId,
        ?int $equipmentComponentId,
    ): WorkTarget;
}
