<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\ValueObject\EntityId;

final readonly class PauseForPartsHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
    ) {}

    public function handle(PauseForPartsCommand $command): void
    {
        $task = $this->tasks->getById(new EntityId($command->productionTaskId));
        $task->pauseForParts();
        $this->tasks->save($task);
        $this->events->publish($task->pullDomainEvents());
    }
}
