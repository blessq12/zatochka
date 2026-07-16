<?php

namespace App\Application\Order\ServiceType;

use App\Domain\Order\VO\OrderServiceType;
use App\Shared\Domain\DomainException;

final readonly class OrderItemBuildStrategyResolver
{
    public function __construct(
        private SharpeningOrderItemBuildStrategy $sharpening,
        private RepairOrderItemBuildStrategy $repair,
    ) {}

    public function for(OrderServiceType $serviceType): OrderItemBuildStrategy
    {
        return match ($serviceType) {
            OrderServiceType::Sharpening => $this->sharpening,
            OrderServiceType::Repair => $this->repair,
        };
    }

    public function forValue(string $serviceType): OrderItemBuildStrategy
    {
        $type = OrderServiceType::tryFrom($serviceType)
            ?? throw new DomainException('Unknown order service type.');

        return $this->for($type);
    }
}
