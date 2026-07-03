<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\ReturnOrderForReworkCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Event\OrderReturnedForRework;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;
use DateTimeImmutable;

final class ReturnOrderForReworkHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(ReturnOrderForReworkCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);

        $updated = $order->returnForRework(
            $command->feedback,
            $command->managerId,
            new DateTimeImmutable,
        );
        $saved = $this->orders->save($updated);

        event(new OrderReturnedForRework($saved));

        return $saved;
    }
}
