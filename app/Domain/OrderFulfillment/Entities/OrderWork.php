<?php

namespace App\Domain\OrderFulfillment\Entities;

final readonly class OrderWork
{
    public function __construct(
        public ?int $id,
        public string $description,
        public ?string $price,
        public int $sortOrder,
    ) {}
}
