<?php

namespace App\Domain\Catalog\Entities;

final class PriceItem
{
    public function __construct(
        private ?int $id,
        private int $priceBlockId,
        private string $name,
        private string $price,
        private ?string $description,
        private int $sortOrder,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function priceBlockId(): int
    {
        return $this->priceBlockId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): string
    {
        return $this->price;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}
