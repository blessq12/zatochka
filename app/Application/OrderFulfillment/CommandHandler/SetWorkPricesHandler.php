<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\SetWorkPricesCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderWork;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
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

        if ($command->pricesByToolType !== []) {
            $order = $this->applyPricesByToolType($order, $command->pricesByToolType);
        }

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

    /**
     * @param  array<string, string|null>  $pricesByToolType
     */
    private function applyPricesByToolType(Order $order, array $pricesByToolType): Order
    {
        if (! $order->isSharpening()) {
            throw new OrderPolicyViolation('Цены по типу инструмента доступны только для заточки.');
        }

        foreach ($pricesByToolType as $toolType => $unitPrice) {
            $order = $order->setToolUnitPriceForType(
                (string) $toolType,
                $unitPrice !== null && $unitPrice !== '' ? (string) $unitPrice : null,
            );
        }

        return $order;
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
