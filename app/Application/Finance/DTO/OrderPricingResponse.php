<?php

namespace App\Application\Finance\DTO;

final readonly class OrderPricingResponse
{
    /**
     * @param  list<array{id: int|null, order_item_id: int, amount: string}>  $lines
     */
    public function __construct(
        public int $id,
        public int $orderId,
        public string $status,
        public string $total,
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
            'status' => $this->status,
            'total' => $this->total,
            'lines' => $this->lines,
        ];
    }
}
