<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class FinishProductionTaskHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private OrderRepository $orders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(FinishProductionTaskCommand $command): void
    {
        $task = $this->tasks->getById(new EntityId($command->productionTaskId));
        $order = $this->orders->getById($task->orderId());

        foreach ($order->items() as $item) {
            if ($item->isFullyRejected()) {
                continue;
            }

            $hasWork = false;

            foreach ($task->works() as $work) {
                if ($work->orderItemId->equals($item->id())) {
                    $hasWork = true;

                    break;
                }
            }

            if (! $hasWork) {
                throw new DomainException(sprintf(
                    'Item #%d has no completed works.',
                    $item->id()->value,
                ));
            }
        }

        if ($task->status() === ProductionStatus::WaitingParts) {
            $task->resumeFromParts();
        }

        if ($task->status() === ProductionStatus::InWork) {
            $task->completeWork();
        }

        if ($task->status() !== ProductionStatus::WorkCompleted) {
            throw new DomainException('Production task cannot be finished from current status.');
        }

        $task->completeProduction();
        $this->tasks->save($task);
        $this->events->publish($task->pullDomainEvents());
    }
}
