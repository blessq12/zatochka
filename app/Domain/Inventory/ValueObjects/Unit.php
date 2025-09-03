<?php

namespace App\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

class Unit
{
    private string $value;

    private const ALLOWED_UNITS = [
        'шт',
        'кг',
        'г',
        'л',
        'мл',
        'м',
        'см',
        'мм',
        'кв.м',
        'куб.м',
        'компл',
        'упак',
        'банка',
        'бутылка',
        'рулон',
        'лист',
        'пачка'
    ];

    private function __construct(string $value)
    {
        $this->ensureValidUnit($value);
        $this->value = trim($value);
    }

    private function ensureValidUnit(string $value): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Unit cannot be empty');
        }

        if (strlen($value) > 20) {
            throw new InvalidArgumentException('Unit cannot exceed 20 characters');
        }

        if (!in_array(trim($value), self::ALLOWED_UNITS)) {
            throw new InvalidArgumentException('Invalid unit: ' . $value);
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

    public function equals(Unit $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function getAllowedUnits(): array
    {
        return self::ALLOWED_UNITS;
    }
}
