<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\ValueObject\EntityId;

final readonly class RemoveMasterWorkHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RemoveMasterWorkCommand $command): void
    {
        $task = $this->tasks->getById(new EntityId($command->productionTaskId));
        $task->removeWork(new EntityId($command->workId), new EntityId($command->masterId));
        $this->tasks->save($task);
        $this->events->publish($task->pullDomainEvents());
    }
}
