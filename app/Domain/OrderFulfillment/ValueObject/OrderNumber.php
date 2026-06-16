<?php

namespace App\Domain\OrderFulfillment\ValueObject;

final readonly class OrderNumber
{
    public function __construct(
        public string $value,
    ) {}

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
