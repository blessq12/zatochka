<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Pricing\Repository\WorkPriceRepository;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\Domain\DomainException;

final readonly class ReturnOrderToMasterHandler
{
    public function __construct(
        private OrderRepository $orders,
        private ProductionTaskRepository $tasks,
        private WorkPriceRepository $workPrices,
        private DomainEventPublisher $events,
    ) {}

    public function handle(ReturnOrderToMasterCommand $command): void
    {
        $orderId = new OrderId($command->orderId);
        $order = $this->orders->getById($orderId);
        $task = $this->tasks->findByOrderId($orderId);

        if ($task === null) {
            throw new DomainException('Production task not found for order.');
        }

        $order->returnToMasterWork($command->reason);
        $task->reopenForRework();

        $performedWorkIds = array_map(
            static fn ($work): int => $work->id->value,
            $task->works(),
        );
        $this->workPrices->deleteByPerformedWorkIds($performedWorkIds);

        $this->orders->save($order);
        $this->tasks->save($task);
        $this->events->publish([
            ...$order->pullDomainEvents(),
            ...$task->pullDomainEvents(),
        ]);
    }
}
