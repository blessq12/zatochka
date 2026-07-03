<?php

namespace App\Domain\Pricing\Entity;

use App\Domain\Pricing\Enum\PricePrefix;

final class PriceItem
{
    public function __construct(
        private ?int $id,
        private int $priceBlockId,
        private string $name,
        private string $price,
        private ?PricePrefix $pricePrefix,
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

    public function pricePrefix(): ?PricePrefix
    {
        return $this->pricePrefix;
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
