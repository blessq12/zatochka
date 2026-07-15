<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Domain\Workshop\Service\MasterQueueService;
use App\Shared\ValueObject\EntityId;

final readonly class OpenProductionTaskHandler
{
    public function __construct(
        private MasterQueueService $queue,
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
    ) {}

    public function handle(OpenProductionTaskCommand $command): void
    {
        $task = $this->queue->openTask(
            new EntityId($command->productionTaskId),
            new EntityId($command->orderItemId),
        );

        $this->tasks->save($task);
        $this->events->publish($task->pullDomainEvents());
    }
}
