<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\UpdateInternalNotesCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Event\InternalNotesUpdated;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class UpdateInternalNotesHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(UpdateInternalNotesCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);
        $this->assertMaster($order, $command->masterId);

        $saved = $this->orders->save($order->updateInternalNotes($command->notes));

        event(new InternalNotesUpdated($saved));

        return $saved;
    }

    private function assertMaster(Order $order, int $masterId): void
    {
        if ($order->masterId() !== $masterId) {
            throw new OrderPolicyViolation('Заказ назначен другому мастеру.');
        }
    }
}
