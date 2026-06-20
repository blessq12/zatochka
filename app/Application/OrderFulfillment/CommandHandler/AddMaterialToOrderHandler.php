<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\AddMaterialToOrderCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderMaterial;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;
use App\Domain\Warehouse\Enum\WarehouseItemType;
use App\Domain\Warehouse\Exception\WarehouseItemNotFoundException;
use App\Domain\Warehouse\Repository\WarehouseItemRepositoryInterface;

final class AddMaterialToOrderHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
        private WarehouseItemRepositoryInterface $warehouseItems,
    ) {}

    public function handle(AddMaterialToOrderCommand $command): Order
    {
        $item = $this->warehouseItems->findById($command->warehouseItemId);

        if ($item === null || $item->id() === null) {
            throw WarehouseItemNotFoundException::withId($command->warehouseItemId);
        }

        $order = $this->orderLoader->load($command->orderId);
        $this->assertItemAllowedForOrder($order, $item->type());

        $unitPrice = $item->price();
        $totalPrice = bcmul($command->quantity, $unitPrice, 2);

        $material = new OrderMaterial(
            id: null,
            warehouseItemId: $item->id(),
            quantity: $command->quantity,
            unitPrice: $unitPrice,
            totalPrice: $totalPrice,
        );

        return $this->orders->save($order->addMaterial($material));
    }

    private function assertItemAllowedForOrder(Order $order, WarehouseItemType $type): void
    {
        if ($type === WarehouseItemType::SparePart && ! $this->orderIsRepair($order)) {
            throw new OrderPolicyViolation('Запчасти можно добавлять только в заказы на ремонт.');
        }
    }

    private function orderIsRepair(Order $order): bool
    {
        return in_array('repair', $order->serviceTypes(), true);
    }
}
