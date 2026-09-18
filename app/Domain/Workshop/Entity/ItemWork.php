<?php

namespace App\Domain\Workshop\Entity;

use App\Shared\Domain\DomainException;

final class ItemWork
{
    /**
     * @param  list<WorkEntry>  $works
     */
    public function __construct(
        private ?int $id,
        private int $orderItemId,
        private ?int $completedQty,
        private array $works = [],
    ) {
        if ($orderItemId < 1) {
            throw new DomainException('order_item_id is required.');
        }
        if ($completedQty !== null && $completedQty < 0) {
            throw new DomainException('completed_qty must be >= 0.');
        }
    }

    public static function create(int $orderItemId): self
    {
        return new self(null, $orderItemId, null, []);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function orderItemId(): int
    {
        return $this->orderItemId;
    }

    public function completedQty(): ?int
    {
        return $this->completedQty;
    }

    /**
     * @return list<WorkEntry>
     */
    public function works(): array
    {
        return $this->works;
    }

    /**
     * @param  list<WorkEntry>  $works
     */
    public function replaceWorks(?int $completedQty, array $works, ?int $maxQty = null): void
    {
        if ($completedQty !== null && $completedQty < 0) {
            throw new DomainException('completed_qty must be >= 0.');
        }
        if ($completedQty !== null && $maxQty !== null && $completedQty > $maxQty) {
            throw new DomainException('completed_qty must be <= declared quantity.');
        }

        $this->completedQty = $completedQty;
        $this->works = $works;
    }
}
