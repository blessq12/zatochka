<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ResumeFromPartsHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
    ) {}

    public function handle(ResumeFromPartsCommand $command): void
    {
        $task = $this->tasks->getById(new EntityId($command->productionTaskId));
        $task->resumeFromParts();
        $this->tasks->save($task);
        $this->events->publish($task->pullDomainEvents());
    }
}
