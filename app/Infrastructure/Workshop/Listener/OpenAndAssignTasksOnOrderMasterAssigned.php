<?php

namespace App\Infrastructure\Workshop\Listener;

use App\Application\Workshop\Command\AssignMasterCommand;
use App\Application\Workshop\Command\AssignMasterHandler;
use App\Application\Workshop\Command\OpenProductionTaskCommand;
use App\Application\Workshop\Command\OpenProductionTaskHandler;
use App\Domain\Order\Event\OrderMasterAssigned;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\Domain\DomainException;

final readonly class OpenAndAssignTasksOnOrderMasterAssigned
{
    public function __construct(
        private OpenProductionTaskHandler $openProductionTask,
        private AssignMasterHandler $assignMaster,
        private ProductionTaskRepository $tasks,
    ) {}

    public function handle(OrderMasterAssigned $event): void
    {
        $orderId = $event->orderId;
        $masterId = $event->masterId;

        $this->openProductionTask->handle(new OpenProductionTaskCommand(
            $orderId->value,
        ));

        $task = $this->tasks->findByOrderId($orderId);

        if ($task === null) {
            return;
        }

        if ($task->masterId() === null) {
            $this->assignMaster->handle(new AssignMasterCommand(
                $task->id()->value,
                $masterId->value,
            ));

            return;
        }

        if (! $task->masterId()->equals($masterId)) {
            throw new DomainException('Order already has another master assigned on production task.');
        }
    }
}
