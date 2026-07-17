<?php

namespace App\Application\Workshop\WorkAttachment;

use App\Application\Workshop\DTO\OrderProductionContextDTO;
use App\Domain\Order\VO\OrderServiceType;
use App\Shared\Domain\DomainException;

final readonly class WorkAttachmentStrategyResolver
{
    public function __construct(
        private SharpeningWorkAttachmentStrategy $sharpening,
        private RepairWorkAttachmentStrategy $repair,
    ) {}

    public function for(OrderProductionContextDTO $context): WorkAttachmentStrategy
    {
        return match ($context->serviceType) {
            OrderServiceType::Sharpening->value => $this->sharpening,
            OrderServiceType::Repair->value => $this->repair,
            default => throw new DomainException('Unknown order service type.'),
        };
    }
}
