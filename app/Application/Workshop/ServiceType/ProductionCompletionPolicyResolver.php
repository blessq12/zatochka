<?php

namespace App\Application\Workshop\ServiceType;

use App\Application\Workshop\DTO\OrderProductionContextDTO;
use App\Domain\Order\VO\OrderServiceType;
use App\Shared\Domain\DomainException;

final readonly class ProductionCompletionPolicyResolver
{
    public function __construct(
        private SharpeningProductionCompletionPolicy $sharpening,
        private RepairProductionCompletionPolicy $repair,
    ) {}

    public function for(OrderProductionContextDTO $context): ProductionCompletionPolicy
    {
        return match ($context->serviceType) {
            OrderServiceType::Sharpening->value => $this->sharpening,
            OrderServiceType::Repair->value => $this->repair,
            default => throw new DomainException('Unknown order service type.'),
        };
    }
}
