<?php

namespace App\Application\Workshop\DTO;

final readonly class OrderProductionContextDTO
{
    /**
     * @param list<OrderProductionItemDTO> $items
     */
    public function __construct(
        public string $orderId,
        public string $serviceType,
        public string $status,
        public array $items,
    ) {}

    public function item(int $orderItemId): ?OrderProductionItemDTO
    {
        foreach ($this->items as $item) {
            if ($item->orderItemId === $orderItemId) {
                return $item;
            }
        }

        return null;
    }
}
