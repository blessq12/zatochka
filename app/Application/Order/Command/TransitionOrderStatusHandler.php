<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;
use App\Shared\Domain\DomainException;

final readonly class TransitionOrderStatusHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderResponseAssembler $assembler,
    ) {}

    public function handle(int $orderId, string $status): ?OrderResponse
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            return null;
        }

        $target = OrderStatus::tryFrom($status)
            ?? throw new DomainException('Invalid status.');

        $order->transitionTo($target);

        return $this->assembler->assemble($this->orders->save($order));
    }
}
