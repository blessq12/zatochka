<?php

namespace App\Domain\Workshop\Aggregate;

use App\Domain\Workshop\Entity\ItemWork;
use App\Domain\Workshop\Entity\WorkEntry;
use App\Domain\Workshop\WorkshopJobStatus;
use App\Shared\Domain\DomainException;

final class WorkshopJob
{
    /**
     * @param  list<ItemWork>  $items
     */
    public function __construct(
        private ?int $id,
        private int $orderId,
        private int $masterId,
        private WorkshopJobStatus $status,
        private array $items,
    ) {
        if ($orderId < 1 || $masterId < 1) {
            throw new DomainException('order_id and master_id are required.');
        }
        if ($items === []) {
            throw new DomainException('Workshop job must have at least one item work.');
        }
    }

    /**
     * @param  list<int>  $orderItemIds
     */
    public static function accept(int $orderId, int $masterId, array $orderItemIds): self
    {
        $unique = array_values(array_unique(array_map('intval', $orderItemIds)));
        if ($unique === []) {
            throw new DomainException('order_item_ids are required.');
        }

        $items = array_map(
            static fn (int $orderItemId): ItemWork => ItemWork::create($orderItemId),
            $unique,
        );

        return new self(null, $orderId, $masterId, WorkshopJobStatus::Open, $items);
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

    public function masterId(): int
    {
        return $this->masterId;
    }

    public function status(): WorkshopJobStatus
    {
        return $this->status;
    }

    /**
     * @return list<ItemWork>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function assertOwnedBy(int $masterId): void
    {
        if ($this->masterId !== $masterId) {
            throw new DomainException('Workshop job belongs to another master.');
        }
    }

    public function assertOpen(): void
    {
        if ($this->status !== WorkshopJobStatus::Open) {
            throw new DomainException('Workshop job is not open.');
        }
    }

    /**
     * @param  list<string>  $workTitles
     */
    public function updateItemWork(
        int $orderItemId,
        ?int $completedQty,
        array $workTitles,
        ?int $maxQty = null,
    ): void {
        $this->assertOpen();

        foreach ($this->items as $item) {
            if ($item->orderItemId() !== $orderItemId) {
                continue;
            }

            $works = [];
            foreach (array_values($workTitles) as $index => $title) {
                $works[] = WorkEntry::create((string) $title, $index);
            }
            $item->replaceWorks($completedQty, $works, $maxQty);

            return;
        }

        throw new DomainException('Order item is not part of this workshop job.');
    }

    public function complete(): void
    {
        $this->assertOpen();
        $this->status = WorkshopJobStatus::Completed;
    }
}
