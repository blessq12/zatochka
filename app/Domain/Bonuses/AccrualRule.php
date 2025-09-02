<?php

declare(strict_types=1);

namespace App\Domain\Bonuses;

final class AccrualRule
{
    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    private string $type;
    private float $value;

    private function __construct(string $type, float $value)
    {
        if (!in_array($type, [self::TYPE_PERCENT, self::TYPE_FIXED], true)) {
            throw new \InvalidArgumentException('Invalid accrual rule type: ' . $type);
        }
        if ($value < 0) {
            throw new \InvalidArgumentException('Accrual value cannot be negative');
        }
        $this->type = $type;
        $this->value = $value;
    }

    public static function percent(float $percent): self
    {
        return new self(self::TYPE_PERCENT, $percent);
    }

    public static function fixed(float $amount): self
    {
        return new self(self::TYPE_FIXED, $amount);
    }

    public function calculateForOrderTotal(float $orderTotal): BonusAmount
    {
        $amount = $this->type === self::TYPE_PERCENT
            ? ($orderTotal * $this->value / 100.0)
            : $this->value;

        return BonusAmount::fromFloat($amount);
    }
}
