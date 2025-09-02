<?php

declare(strict_types=1);

namespace App\Domain\Bonuses;

/**
 * Value Object representing a non-negative bonus amount.
 * Rounds up on construction as per business rule (ceil).
 */
final class BonusAmount
{
    private int $value;

    private function __construct(int $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('BonusAmount cannot be negative');
        }
        $this->value = $value;
    }

    public static function fromFloat(float $amount): self
    {
        // Business rule: round up
        $rounded = (int) ceil($amount);
        return new self($rounded);
    }

    public static function fromInt(int $amount): self
    {
        return new self($amount);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function isZero(): bool
    {
        return $this->value === 0;
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(self $other): self
    {
        $result = $this->value - $other->value;
        if ($result < 0) {
            throw new \DomainException('BonusAmount cannot be negative after subtraction');
        }
        return new self($result);
    }
}
