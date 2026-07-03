<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderWork;
use App\Domain\OrderFulfillment\Event\WorkAdded;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class AddWorkHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(AddWorkCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);

        if ($order->masterId() !== $command->masterId) {
            throw new OrderPolicyViolation('Заказ назначен другому мастеру.');
        }

        $toolType = self::resolveToolType($order, $command->toolType);

        $work = new OrderWork(
            id: null,
            description: $command->description,
            price: null,
            sortOrder: $order->nextWorkSortOrder(),
            toolType: $toolType,
        );

        $saved = $this->orders->save($order->addWork($work));

        event(new WorkAdded($saved, $work));

        return $saved;
    }

    private static function resolveToolType(Order $order, ?string $toolType): ?string
    {
        if (! $order->isSharpening()) {
            return null;
        }

        if (in_array('repair', $order->serviceTypes(), true) && ($toolType === null || $toolType === '')) {
            return null;
        }

        if ($toolType === null || $toolType === '') {
            throw new OrderPolicyViolation('Укажите тип инструмента для работы.');
        }

        foreach ($order->tools() as $tool) {
            if ($tool->toolType === $toolType) {
                return $toolType;
            }
        }

        throw new OrderPolicyViolation('Тип инструмента не найден в заказе.');
    }
}
