<?php

namespace App\Application\Workshop\ServiceType;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\VO\OrderServiceType;

final readonly class ProductionCompletionPolicyResolver
{
    public function __construct(
        private SharpeningProductionCompletionPolicy $sharpening,
        private RepairProductionCompletionPolicy $repair,
    ) {}

    public function for(Order $order): ProductionCompletionPolicy
    {
        return match ($order->serviceType()) {
            OrderServiceType::Sharpening => $this->sharpening,
            OrderServiceType::Repair => $this->repair,
        };
    }
}
