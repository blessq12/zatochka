<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\Repository\OrderRepository;

final readonly class AssignMasterHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderResponseAssembler $assembler,
    ) {}

    public function handle(int $orderId, int $masterId): ?OrderResponse
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            return null;
        }

        $order->assignMaster($masterId);

        return $this->assembler->assemble($this->orders->save($order));
    }
}
