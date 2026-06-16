<?php

namespace App\Domain\Catalog\Entities;

use App\Domain\Catalog\Enums\PriceType;

final class PriceBlock
{
    public function __construct(
        private ?int $id,
        private PriceType $type,
        private string $title,
        private int $sortOrder,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function type(): PriceType
    {
        return $this->type;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}
