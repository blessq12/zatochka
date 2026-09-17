<?php

namespace App\Domain\Warehouse\Aggregate;

use App\Domain\Warehouse\Entity\IssueLine;
use App\Shared\Domain\DomainException;

final class OrderIssue
{
    /**
     * @param  list<IssueLine>  $lines
     */
    public function __construct(
        private ?int $id,
        private int $orderId,
        private array $lines,
    ) {
        if ($orderId < 1) {
            throw new DomainException('order_id is required.');
        }
        $this->assertUniqueStockItems($lines);
    }

    public static function forOrder(int $orderId): self
    {
        return new self(null, $orderId, []);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return list<IssueLine>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * @param  list<array{stock_item_id: int, qty: string}>  $lines
     */
    public function replaceLines(array $lines): void
    {
        $mapped = [];
        foreach ($lines as $row) {
            $mapped[] = IssueLine::create(
                (int) $row['stock_item_id'],
                (string) $row['qty'],
            );
        }
        $this->assertUniqueStockItems($mapped);
        $this->lines = $mapped;
    }

    /**
     * @param  list<IssueLine>  $lines
     */
    private function assertUniqueStockItems(array $lines): void
    {
        $seen = [];
        foreach ($lines as $line) {
            $id = $line->stockItemId();
            if (isset($seen[$id])) {
                throw new DomainException('Duplicate stock_item_id in issue lines.');
            }
            $seen[$id] = true;
        }
    }
}
