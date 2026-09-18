<?php

namespace App\Application\Workshop\DTO;

final readonly class WorkshopJobResponse
{
    /**
     * @param  list<array{
     *     id: int|null,
     *     order_item_id: int,
     *     completed_qty: int|null,
     *     works: list<array{id: int|null, title: string, position: int, equipment_module_id: int|null}>
     * }>  $items
     */
    public function __construct(
        public int $id,
        public int $orderId,
        public int $masterId,
        public string $status,
        public array $items,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'master_id' => $this->masterId,
            'status' => $this->status,
            'items' => $this->items,
        ];
    }
}
