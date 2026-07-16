<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class FinishProductionTaskHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
    ) {}

    public function handle(FinishProductionTaskCommand $command): void
    {
        $task = $this->tasks->getById(new EntityId($command->productionTaskId));

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
