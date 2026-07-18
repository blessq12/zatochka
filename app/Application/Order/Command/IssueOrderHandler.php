<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\UnitOfWork;
use App\Domain\Finance\VO\PaymentMethod;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Shared\Domain\DomainException;

final readonly class IssueOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(IssueOrderCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $method = $command->paymentMethod !== null ? trim($command->paymentMethod) : null;
            if ($method === '') {
                $method = null;
            }

            if ($method !== null && PaymentMethod::tryFrom($method) === null) {
                throw new DomainException('Unknown payment method.');
            }

            $order = $this->orders->getById(new OrderId($command->orderId));
            $order->issue($method);
            $this->orders->save($order);
            $this->events->publish($order->pullDomainEvents());
        });
    }
}
