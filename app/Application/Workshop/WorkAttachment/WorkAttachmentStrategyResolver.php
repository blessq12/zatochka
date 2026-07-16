<?php

namespace App\Application\Workshop\WorkAttachment;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\VO\OrderServiceType;

final readonly class WorkAttachmentStrategyResolver
{
    public function __construct(
        private SharpeningWorkAttachmentStrategy $sharpening,
        private RepairWorkAttachmentStrategy $repair,
    ) {}

    public function for(Order $order): WorkAttachmentStrategy
    {
        return match ($order->serviceType()) {
            OrderServiceType::Sharpening => $this->sharpening,
            OrderServiceType::Repair => $this->repair,
        };
    }
}
