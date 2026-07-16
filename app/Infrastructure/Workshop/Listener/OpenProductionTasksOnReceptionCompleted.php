<?php

namespace App\Infrastructure\Workshop\Listener;

use App\Application\Workshop\Command\OpenProductionTaskCommand;
use App\Application\Workshop\Command\OpenProductionTaskHandler;
use App\Domain\Order\Event\ReceptionCompleted;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;

final readonly class OpenProductionTasksOnReceptionCompleted
{
    public function __construct(
        private OpenProductionTaskHandler $openProductionTask,
        private ProductionTaskRepository $tasks,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function handle(ReceptionCompleted $event): void
    {
        $orderId = $event->orderId;

        if ($this->tasks->findByOrderId($orderId) !== null) {
            return;
        }

        $this->openProductionTask->handle(new OpenProductionTaskCommand(
            $this->ids->next('production_task')->value,
            $orderId->value,
        ));
    }
}
