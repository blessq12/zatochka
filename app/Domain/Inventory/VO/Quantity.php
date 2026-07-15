<?php

namespace App\Domain\Inventory\VO;

use App\Shared\Domain\DomainException;

final readonly class Quantity
{
    public function __construct(
        public string $value,
    ) {
        if (! is_numeric($this->value) || (float) $this->value < 0) {
            throw new DomainException('Quantity must be a non-negative number.');
        }
    }

    public function add(self $other): self
    {
        return new self(number_format((float) $this->value + (float) $other->value, 3, '.', ''));
    }

    public function subtract(self $other): self
    {
        $result = (float) $this->value - (float) $other->value;

        if ($result < 0) {
            throw new DomainException('Insufficient stock quantity.');
        }

        return new self(number_format($result, 3, '.', ''));
    }
}
