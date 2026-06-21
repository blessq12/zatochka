<?php

namespace App\Domain\OrderFulfillment\Entity;

final readonly class OrderTool
{
    public function __construct(
        public ?int $id,
        public string $toolType,
        public int $quantity,
        public ?string $name = null,
    ) {}
}
