<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Workshop\Entity\Diagnosis;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class CompleteDiagnosisHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
    ) {}

    public function handle(CompleteDiagnosisCommand $command): void
    {
        $task = $this->tasks->getById(new EntityId($command->productionTaskId));
        $task->completeDiagnosis(new Diagnosis(
            new EntityId($command->diagnosisId),
            $command->summary,
            new DateTimeImmutable(),
            $command->technicalNotes,
        ));
        $this->tasks->save($task);
        $this->events->publish($task->pullDomainEvents());
    }
}
