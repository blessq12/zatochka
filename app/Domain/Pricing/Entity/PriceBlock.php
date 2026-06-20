<?php

namespace App\Domain\Pricing\Entity;

use App\Domain\Pricing\Enum\PriceType;

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
