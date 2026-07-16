<?php

namespace App\Application\Pricing\ServiceType;

use App\Domain\Order\VO\OrderServiceType;
use App\Shared\Domain\DomainException;

final readonly class WorkPricingPolicyResolver
{
    public function __construct(
        private SharpeningWorkPricingPolicy $sharpening,
        private RepairWorkPricingPolicy $repair,
    ) {}

    public function for(OrderServiceType $serviceType): WorkPricingPolicy
    {
        return match ($serviceType) {
            OrderServiceType::Sharpening => $this->sharpening,
            OrderServiceType::Repair => $this->repair,
        };
    }

    public function forValue(string $serviceType): WorkPricingPolicy
    {
        $type = OrderServiceType::tryFrom($serviceType)
            ?? throw new DomainException('Unknown order service type.');

        return $this->for($type);
    }
}
