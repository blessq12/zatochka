<?php

namespace App\Domain\OrderFulfillment\Entities;

final readonly class OrderTool
{
    public function __construct(
        public ?int $id,
        public string $toolType,
        public int $quantity,
    ) {}
}
