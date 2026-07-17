<?php

namespace App\Application\Workshop\Command;

use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\Domain\DomainException;

final readonly class EnsureProductionTaskOpenedAndAssignedHandler
{
    public function __construct(
        private OpenProductionTaskHandler $openProductionTask,
        private AssignMasterHandler $assignMaster,
        private ProductionTaskRepository $tasks,
    ) {}

    public function handle(EnsureProductionTaskOpenedAndAssignedCommand $command): void
    {
        $orderId = new OrderId($command->orderId);

        $this->openProductionTask->handle(new OpenProductionTaskCommand(
            $command->orderId,
        ));

        $task = $this->tasks->findByOrderId($orderId);

        if ($task === null) {
            return;
        }

        if ($task->masterId() === null) {
            $this->assignMaster->handle(new AssignMasterCommand(
                $task->id()->value,
                $command->masterId,
            ));

            return;
        }

        if ($task->masterId()->value !== $command->masterId) {
            throw new DomainException('Order already has another master assigned on production task.');
        }
    }
}
