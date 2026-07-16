<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\Domain\DomainException;

final readonly class ReopenProductionTaskForReworkHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
    ) {}

    public function handle(ReopenProductionTaskForReworkCommand $command): void
    {
        $task = $this->tasks->findByOrderId(new OrderId($command->orderId));

        if ($task === null) {
            throw new DomainException('Production task not found for order.');
        }

        $task->reopenForRework();
        $this->tasks->save($task);
        $this->events->publish($task->pullDomainEvents());
    }
}
