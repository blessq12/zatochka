<?php

namespace App\Application\Order\Command;

use App\Application\Order\ReadPort\OrderContainerReadPort;
use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\UnitOfWork;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderItemStatus;
use App\Shared\Domain\DomainException;

final readonly class MarkOrderReadyHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderContainerReadPort $orderContainers,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(MarkOrderReadyCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $order = $this->orders->getById(new OrderId($command->orderId));
            $container = $this->orderContainers->findById($command->orderId);

            if ($container === null || $container->productionTask === null) {
                throw new DomainException('Production task not found for order.');
            }

            foreach ($container->items as $item) {
                if ($item->status === OrderItemStatus::Rejected->value || $item->repairableQuantity < 1) {
                    continue;
                }

                if ($item->works === []) {
                    throw new DomainException(sprintf(
                        'Item #%d has no completed works to price.',
                        $item->id,
                    ));
                }

                foreach ($item->works as $work) {
                    $price = $work['price'] ?? null;

                    if (! is_array($price) || ! ($price['calculated'] ?? false)) {
                        throw new DomainException(sprintf(
                            'Work #%d does not have a calculated price yet.',
                            $work['id'],
                        ));
                    }
                }
            }

            $order->markReady();
            $this->orders->save($order);
            $this->events->publish($order->pullDomainEvents());
        });
    }
}
