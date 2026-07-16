<?php

namespace App\Infrastructure\Order\Listener;

use App\Application\Order\Command\MarkOrderWorksCompletedCommand;
use App\Application\Order\Command\MarkOrderWorksCompletedHandler;
use App\Domain\Workshop\Event\ProductionCompleted;

/**
 * When master finishes the production task: finalize items and move order to works_completed.
 */
final readonly class MarkOrderWorksCompletedOnProductionCompleted
{
    public function __construct(
        private MarkOrderWorksCompletedHandler $markWorksCompleted,
    ) {}

    public function handle(ProductionCompleted $event): void
    {
        $this->markWorksCompleted->handle(new MarkOrderWorksCompletedCommand(
            $event->orderId->value,
        ));
    }
}
