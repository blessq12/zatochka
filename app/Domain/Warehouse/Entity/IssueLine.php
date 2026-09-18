<?php

namespace App\Domain\Warehouse\Entity;

use App\Domain\Warehouse\Aggregate\StockItem;
use App\Shared\Domain\DomainException;

final class IssueLine
{
    public function __construct(
        private ?int $id,
        private int $stockItemId,
        private string $qty,
    ) {
        if ($stockItemId < 1) {
            throw new DomainException('stock_item_id is required.');
        }
        $normalized = StockItem::normalizeQty($qty);
        if ((float) $normalized <= 0) {
            throw new DomainException('issue line qty must be > 0.');
        }
        $this->qty = $normalized;
    }

    public static function create(int $stockItemId, string $qty): self
    {
        return new self(null, $stockItemId, $qty);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function stockItemId(): int
    {
        return $this->stockItemId;
    }

    public function qty(): string
    {
        return $this->qty;
    }
}
