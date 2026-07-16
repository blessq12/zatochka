<?php

namespace App\Infrastructure\Workshop\Listener;

use App\Application\Workshop\Command\ReopenProductionTaskForReworkCommand;
use App\Application\Workshop\Command\ReopenProductionTaskForReworkHandler;
use App\Domain\Order\Event\OrderReturnedToMaster;

final readonly class ReopenProductionTaskOnOrderReturnedToMaster
{
    public function __construct(
        private ReopenProductionTaskForReworkHandler $reopen,
    ) {}

    public function handle(OrderReturnedToMaster $event): void
    {
        $this->reopen->handle(new ReopenProductionTaskForReworkCommand(
            $event->orderId->value,
        ));
    }
}
