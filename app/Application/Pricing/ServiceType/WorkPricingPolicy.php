<?php

namespace App\Application\Pricing\ServiceType;

use App\Domain\Order\VO\OrderServiceType;
use App\Shared\Domain\DomainException;

interface WorkPricingPolicy
{
    public function modalDescription(): string;

    public function positionFieldLabel(): string;

    public function amountFieldLabel(): string;

    public function showQuantityColumn(): bool;

    public function lineQuantity(int $repairableQuantity): int;
}
