<?php

namespace App\Infrastructure\Order\Listener;

use App\Application\Order\Command\MarkOrderInProgressCommand;
use App\Application\Order\Command\MarkOrderInProgressHandler;
use App\Domain\Workshop\Event\WorkStarted;

final readonly class MarkOrderInProgressOnWorkStarted
{
    public function __construct(
        private MarkOrderInProgressHandler $markInProgress,
    ) {}

    public function handle(WorkStarted $event): void
    {
        $this->markInProgress->handle(new MarkOrderInProgressCommand(
            $event->orderId->value,
        ));
    }
}
