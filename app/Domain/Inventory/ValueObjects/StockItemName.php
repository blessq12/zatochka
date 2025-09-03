<?php

namespace App\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

class StockItemName
{
    private string $value;

    private function __construct(string $value)
    {
        $this->ensureValidName($value);
        $this->value = trim($value);
    }

    private function ensureValidName(string $value): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Stock item name cannot be empty');
        }

        if (strlen($value) > 255) {
            throw new InvalidArgumentException('Stock item name cannot exceed 255 characters');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(StockItemName $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
