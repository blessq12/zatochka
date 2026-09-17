<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Application\Order\Support\OrderItemMapper;
use App\Domain\Order\Repository\OrderRepository;

final readonly class UpdateOrderItemsHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderResponseAssembler $assembler,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function handle(int $orderId, array $items): ?OrderResponse
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            return null;
        }

        $order->replaceItems(OrderItemMapper::fromPayload($items));

        return $this->assembler->assemble($this->orders->save($order));
    }
}
