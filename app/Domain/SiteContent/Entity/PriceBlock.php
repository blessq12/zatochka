<?php

namespace App\Domain\SiteContent\Entity;

use App\Domain\SiteContent\VO\PriceBlockType;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class PriceBlock
{
    /**
     * @param  list<PriceItem>  $items
     */
    private function __construct(
        private readonly EntityId $id,
        private PriceBlockType $type,
        private string $title,
        private array $items,
        private int $sortOrder,
    ) {}

    /** @param list<PriceItem> $items */
    public static function create(
        EntityId $id,
        PriceBlockType $type,
        string $title,
        array $items,
        int $sortOrder,
    ): self {
        return self::reconstitute($id, $type, $title, $items, $sortOrder);
    }

    /** @param list<PriceItem> $items */
    public static function reconstitute(
        EntityId $id,
        PriceBlockType $type,
        string $title,
        array $items,
        int $sortOrder,
    ): self {
        return new self(
            $id,
            $type,
            self::requireText($title, 'Price block title'),
            array_values($items),
            $sortOrder,
        );
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function type(): PriceBlockType
    {
        return $this->type;
    }

    public function title(): string
    {
        return $this->title;
    }

    /** @return list<PriceItem> */
    public function items(): array
    {
        return $this->items;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    private static function requireText(string $value, string $label): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new DomainException(sprintf('%s is required.', $label));
        }

        return $trimmed;
    }
}
