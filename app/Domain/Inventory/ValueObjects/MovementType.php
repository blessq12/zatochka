<?php

namespace App\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

class MovementType
{
    private string $value;

    private const ALLOWED_TYPES = [
        'in',
        'out',
        'transfer',
        'adjustment',
        'return'
    ];

    private function __construct(string $value)
    {
        $this->ensureValidType($value);
        $this->value = $value;
    }

    private function ensureValidType(string $value): void
    {
        if (!in_array($value, self::ALLOWED_TYPES)) {
            throw new InvalidArgumentException('Invalid movement type: ' . $value);
        }
    }

    public static function in(): self
    {
        return new self('in');
    }

    public static function out(): self
    {
        return new self('out');
    }

    public static function transfer(): self
    {
        return new self('transfer');
    }

    public static function adjustment(): self
    {
        return new self('adjustment');
    }

    public static function return(): self
    {
        return new self('return');
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(MovementType $other): bool
    {
        return $this->value === $other->value;
    }

    public function isIncoming(): bool
    {
        return in_array($this->value, ['in', 'return']);
    }

    public function isOutgoing(): bool
    {
        return in_array($this->value, ['out', 'transfer']);
    }

    public function isAdjustment(): bool
    {
        return $this->value === 'adjustment';
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function getAllowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }
}
