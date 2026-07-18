<?php

namespace App\Domain\SiteContent\Entity;

final class ServicePriceList
{
    /** @param list<PriceBlock> $blocks */
    private function __construct(
        private array $blocks,
    ) {}

    /** @param list<PriceBlock> $blocks */
    public static function create(array $blocks = []): self
    {
        return new self(array_values($blocks));
    }

    /** @param list<PriceBlock> $blocks */
    public static function reconstitute(array $blocks): self
    {
        return new self(array_values($blocks));
    }

    /** @return list<PriceBlock> */
    public function blocks(): array
    {
        return $this->blocks;
    }

    /** @param list<PriceBlock> $blocks */
    public function replaceBlocks(array $blocks): void
    {
        $this->blocks = array_values($blocks);
    }
}
