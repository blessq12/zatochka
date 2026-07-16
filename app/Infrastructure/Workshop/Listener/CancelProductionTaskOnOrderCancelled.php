<?php

namespace App\Infrastructure\Workshop\Listener;

use App\Application\Workshop\Command\CancelProductionTaskCommand;
use App\Application\Workshop\Command\CancelProductionTaskHandler;
use App\Domain\Order\Event\OrderCancelled;

final readonly class CancelProductionTaskOnOrderCancelled
{
    public function __construct(
        private CancelProductionTaskHandler $cancelProductionTask,
    ) {}

    public function handle(OrderCancelled $event): void
    {
        $this->cancelProductionTask->handle(new CancelProductionTaskCommand(
            $event->orderId->value,
        ));
    }
}
