<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\UnitOfWork;
use App\Domain\Workshop\Entity\WorkExecution;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class StartWorkHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(StartWorkCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $task = $this->tasks->getById(new EntityId($command->productionTaskId));
            $task->startWork(new WorkExecution(
                new EntityId($command->workExecutionId),
                $command->description,
                new DateTimeImmutable(),
            ));
            $this->tasks->save($task);
            $this->events->publish($task->pullDomainEvents());
        });
    }
}
