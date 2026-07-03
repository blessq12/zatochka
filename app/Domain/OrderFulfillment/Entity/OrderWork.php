<?php

namespace App\Domain\OrderFulfillment\Entity;

final readonly class OrderWork
{
    public function __construct(
        public ?int $id,
        public string $description,
        public ?string $price,
        public int $sortOrder,
        public ?string $toolType = null,
    ) {}
}
