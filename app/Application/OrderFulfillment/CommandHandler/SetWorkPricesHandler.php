<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\SetWorkPricesCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderWork;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class SetWorkPricesHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(SetWorkPricesCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);

        foreach ($command->pricesBySortOrder as $sortOrder => $price) {
            if ($order->isSharpening()) {
                $work = self::findWork($order, (int) $sortOrder);

                if ($work !== null && $order->workUsesUnitPricing($work)) {
                    $price = Order::workTotalFromUnitPrice(
                        $price !== null && $price !== '' ? (string) $price : null,
                        $order->toolsQuantityForWork($work->toolType),
                    );
                }
            }

            $order = $order->setWorkPrice((int) $sortOrder, $price);
        }

        return $this->orders->save($order);
    }

    private static function findWork(Order $order, int $sortOrder): ?OrderWork
    {
        foreach ($order->works() as $work) {
            if ($work->sortOrder === $sortOrder) {
                return $work;
            }
        }

        return null;
    }
}
