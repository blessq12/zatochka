<?php

namespace App\Application\Workshop\WorkAttachment;

use App\Application\Workshop\DTO\OrderProductionContextDTO;
use App\Domain\Workshop\VO\WorkTarget;

interface WorkAttachmentStrategy
{
    public function resolveTarget(
        OrderProductionContextDTO $context,
        ?int $orderItemId,
        ?int $equipmentComponentId,
    ): WorkTarget;
}
