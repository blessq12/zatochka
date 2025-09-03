<?php

namespace App\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

class Quantity
{
    private int $value;

    private function __construct(int $value)
    {
        $this->ensureValidQuantity($value);
        $this->value = $value;
    }

    private function ensureValidQuantity(int $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Quantity cannot be negative');
        }
    }

    public static function fromInteger(int $value): self
    {
        return new self($value);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function add(Quantity $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(Quantity $other): self
    {
        $newValue = $this->value - $other->value;
        if ($newValue < 0) {
            throw new InvalidArgumentException('Result cannot be negative');
        }
        return new self($newValue);
    }

    public function multiply(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Factor cannot be negative');
        }
        return new self($this->value * $factor);
    }

    public function isZero(): bool
    {
        return $this->value === 0;
    }

    public function isGreaterThan(Quantity $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(Quantity $other): bool
    {
        return $this->value < $other->value;
    }

    public function equals(Quantity $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
