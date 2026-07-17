<?php

namespace App\Infrastructure\Workshop\Listener;

use App\Application\Workshop\Command\EnsureProductionTaskOpenedAndAssignedCommand;
use App\Application\Workshop\Command\EnsureProductionTaskOpenedAndAssignedHandler;
use App\Domain\Order\Event\OrderMasterAssigned;

final readonly class OpenAndAssignTasksOnOrderMasterAssigned
{
    public function __construct(
        private EnsureProductionTaskOpenedAndAssignedHandler $ensureOpenedAndAssigned,
    ) {}

    public function handle(OrderMasterAssigned $event): void
    {
        $this->ensureOpenedAndAssigned->handle(new EnsureProductionTaskOpenedAndAssignedCommand(
            $event->orderId->value,
            $event->masterId->value,
        ));
    }
}
