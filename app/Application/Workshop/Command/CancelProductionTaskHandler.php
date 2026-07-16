<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Repository\ProductionTaskRepository;

final readonly class CancelProductionTaskHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
    ) {}

    public function handle(CancelProductionTaskCommand $command): void
    {
        $task = $this->tasks->findByOrderId(new OrderId($command->orderId));

        if ($task === null) {
            return;
        }

        $task->cancel();
        $this->tasks->save($task);
        $this->events->publish($task->pullDomainEvents());
    }
}
