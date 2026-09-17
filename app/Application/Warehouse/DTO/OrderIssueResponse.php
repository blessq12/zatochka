<?php

namespace App\Application\Warehouse\DTO;

final readonly class OrderIssueResponse
{
    /**
     * @param  list<array{id: int|null, stock_item_id: int, qty: string}>  $lines
     */
    public function __construct(
        public int $id,
        public int $orderId,
        public array $lines,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'lines' => $this->lines,
        ];
    }
}
