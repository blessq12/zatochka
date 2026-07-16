<?php

namespace App\Infrastructure\Workshop\Listener;

use App\Application\Workshop\Command\OpenProductionTaskCommand;
use App\Application\Workshop\Command\OpenProductionTaskHandler;
use App\Domain\Order\Event\ReceptionCompleted;

final readonly class OpenProductionTasksOnReceptionCompleted
{
    public function __construct(
        private OpenProductionTaskHandler $openProductionTask,
    ) {}

    public function handle(ReceptionCompleted $event): void
    {
        $this->openProductionTask->handle(new OpenProductionTaskCommand(
            $event->orderId->value,
        ));
    }
}
