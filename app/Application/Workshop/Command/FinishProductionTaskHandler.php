<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\UnitOfWork;
use App\Application\Workshop\ServiceType\ProductionCompletionPolicyResolver;
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
        private ProductionCompletionPolicyResolver $completionPolicies,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(FinishProductionTaskCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $task = $this->tasks->getById(new EntityId($command->productionTaskId));
            $order = $this->orders->getById($task->orderId());

            $this->completionPolicies->for($order)->assertReadyToFinish($order, $task);

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
        });
    }
}
