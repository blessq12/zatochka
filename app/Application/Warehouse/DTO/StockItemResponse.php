<?php

namespace App\Application\Warehouse\DTO;

final readonly class StockItemResponse
{
    public function __construct(
        public int $id,
        public string $category,
        public string $name,
        public string $qtyOnHand,
        public string $unit,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'name' => $this->name,
            'qty_on_hand' => $this->qtyOnHand,
            'unit' => $this->unit,
        ];
    }
}
