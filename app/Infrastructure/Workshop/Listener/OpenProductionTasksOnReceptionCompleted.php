<?php

namespace App\Infrastructure\Workshop\Listener;

use App\Application\Order\ReadPort\OrderReadPort;
use App\Application\Workshop\Command\OpenProductionTaskCommand;
use App\Application\Workshop\Command\OpenProductionTaskHandler;
use App\Domain\Order\Event\ReceptionCompleted;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;

final readonly class OpenProductionTasksOnReceptionCompleted
{
    public function __construct(
        private OrderReadPort $orders,
        private OpenProductionTaskHandler $openProductionTask,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function handle(ReceptionCompleted $event): void
    {
        $order = $this->orders->findById($event->orderId->value);

        if ($order === null) {
            return;
        }

        foreach ($order->items as $item) {
            $this->openProductionTask->handle(new OpenProductionTaskCommand(
                $this->ids->next('production_task')->value,
                $item->id,
            ));
        }
    }
}
