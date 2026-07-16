<?php

namespace App\Application\Pricing\ServiceType;

final class SharpeningWorkPricingPolicy implements WorkPricingPolicy
{
    public function modalDescription(): string
    {
        return 'Укажите цену за каждую работу по позиции. Итог по работе = цена × количество к выдаче по позиции.';
    }

    public function positionFieldLabel(): string
    {
        return 'Позиция';
    }

    public function amountFieldLabel(): string
    {
        return 'Цена работы за ед.';
    }

    public function showQuantityColumn(): bool
    {
        return true;
    }

    public function lineQuantity(int $repairableQuantity): int
    {
        return max(1, $repairableQuantity);
    }
}
