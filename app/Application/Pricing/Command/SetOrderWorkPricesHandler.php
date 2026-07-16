<?php

namespace App\Application\Pricing\Command;

use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Shared\Domain\DomainException;

final readonly class SetOrderWorkPricesHandler
{
    public function __construct(
        private OrderRepository $orders,
        private SetWorkPriceHandler $setWorkPrice,
    ) {}

    public function handle(SetOrderWorkPricesCommand $command): void
    {
        $order = $this->orders->getById(new OrderId($command->orderId));

        if ($order->status() !== OrderStatus::AwaitingPricing) {
            throw new DomainException('Prices can only be changed while order is awaiting pricing.');
        }

        if ($command->works === []) {
            throw new DomainException('At least one work price is required.');
        }

        foreach ($command->works as $work) {
            $this->setWorkPrice->handle(new SetWorkPriceCommand(
                (int) $work['master_comment_id'],
                (string) $work['base_amount'],
                $command->currency,
            ));
        }
    }
}
