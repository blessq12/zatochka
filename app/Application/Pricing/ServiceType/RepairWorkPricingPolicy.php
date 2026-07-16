<?php

namespace App\Application\Pricing\ServiceType;

final class RepairWorkPricingPolicy implements WorkPricingPolicy
{
    public function modalDescription(): string
    {
        return 'Укажите цену за каждую работу по элементу оборудования.';
    }

    public function positionFieldLabel(): string
    {
        return 'Элемент';
    }

    public function amountFieldLabel(): string
    {
        return 'Цена работы';
    }

    public function showQuantityColumn(): bool
    {
        return false;
    }

    public function lineQuantity(int $repairableQuantity): int
    {
        return 1;
    }
}
