<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;
use App\Shared\Domain\DomainException;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\OrderIssued;
use App\Shared\IntegrationEvents\OrderReturnedToRework;

final readonly class TransitionOrderStatusHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderResponseAssembler $assembler,
        private EventBus $events,
    ) {}

    public function handle(int $orderId, string $status): ?OrderResponse
    {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            return null;
        }

        $target = OrderStatus::tryFrom($status)
            ?? throw new DomainException('Invalid status.');

        $from = $order->status();
        $order->transitionTo($target);
        $saved = $this->orders->save($order);

        if ($from === OrderStatus::WorksCompleted && $target === OrderStatus::InProgress) {
            $this->events->publish(new OrderReturnedToRework($orderId));
        }

        if ($target === OrderStatus::Issued) {
            $this->events->publish(new OrderIssued(
                $orderId,
                $saved->billingType()->value,
            ));
        }

        return $this->assembler->assemble($saved);
    }
}
