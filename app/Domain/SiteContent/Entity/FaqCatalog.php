<?php

namespace App\Domain\SiteContent\Entity;

final class FaqCatalog
{
    /** @param list<FaqItem> $items */
    private function __construct(
        private array $items,
    ) {}

    /** @param list<FaqItem> $items */
    public static function create(array $items = []): self
    {
        return new self(array_values($items));
    }

    /** @param list<FaqItem> $items */
    public static function reconstitute(array $items): self
    {
        return new self(array_values($items));
    }

    /** @return list<FaqItem> */
    public function items(): array
    {
        return $this->items;
    }

    /** @param list<FaqItem> $items */
    public function replaceItems(array $items): void
    {
        $this->items = array_values($items);
    }
}
