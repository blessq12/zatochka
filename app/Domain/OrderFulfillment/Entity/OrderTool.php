<?php

namespace App\Domain\OrderFulfillment\Entity;

final readonly class OrderTool
{
    public function __construct(
        public ?int $id,
        public string $toolType,
        public int $quantity,
        public ?string $name = null,
        public ?string $unitPrice = null,
    ) {}

    public function lineTotal(): ?string
    {
        if ($this->unitPrice === null) {
            return null;
        }

        return bcmul($this->unitPrice, (string) $this->quantity, 2);
    }
}
