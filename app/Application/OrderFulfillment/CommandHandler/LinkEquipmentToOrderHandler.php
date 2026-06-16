<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\LinkEquipmentToOrderCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\Equipment\Exception\EquipmentNotFoundException;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Event\EquipmentLinkedToOrder;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class LinkEquipmentToOrderHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
        private EquipmentRepositoryInterface $equipment,
    ) {}

    public function handle(LinkEquipmentToOrderCommand $command): Order
    {
        $equipment = $this->equipment->findById($command->equipmentId);

        if ($equipment === null) {
            throw EquipmentNotFoundException::withId($command->equipmentId);
        }

        $order = $this->orderLoader->load($command->orderId);
        $updated = $this->orders->save($order->linkEquipment($command->equipmentId));

        event(new EquipmentLinkedToOrder($updated, $command->equipmentId));

        return $updated;
    }
}
