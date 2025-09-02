<?php

declare(strict_types=1);

namespace App\Domain\Bonuses;

final class RedemptionLimit
{
    private ?float $maxPercentOfOrder;

    private function __construct(?float $maxPercentOfOrder)
    {
        if ($maxPercentOfOrder !== null && ($maxPercentOfOrder < 0 || $maxPercentOfOrder > 100)) {
            throw new \InvalidArgumentException('Max percent must be between 0 and 100');
        }
        $this->maxPercentOfOrder = $maxPercentOfOrder;
    }

    public static function none(): self
    {
        return new self(null);
    }

    public static function percent(float $percent): self
    {
        return new self($percent);
    }

    public function maxAllowedForOrderTotal(float $orderTotal): ?BonusAmount
    {
        if ($this->maxPercentOfOrder === null) {
            return null;
        }
        return BonusAmount::fromFloat($orderTotal * $this->maxPercentOfOrder / 100.0);
    }
}
