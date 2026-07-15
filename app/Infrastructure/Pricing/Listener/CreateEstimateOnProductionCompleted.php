<?php

namespace App\Infrastructure\Pricing\Listener;

use App\Application\Pricing\Command\CreateEstimateCommand;
use App\Application\Pricing\Command\CreateEstimateHandler;
use App\Application\Pricing\ReadPort\EstimateReadPort;
use App\Domain\Workshop\Event\ProductionCompleted;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;

final readonly class CreateEstimateOnProductionCompleted
{
    public function __construct(
        private EstimateReadPort $estimates,
        private CreateEstimateHandler $createEstimate,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function handle(ProductionCompleted $event): void
    {
        if ($this->estimates->findByOrderItemId($event->orderItemId->value) !== null) {
            return;
        }

        $this->createEstimate->handle(new CreateEstimateCommand(
            $this->ids->next('estimate')->value,
            $event->orderItemId->value,
            '0.00',
        ));
    }
}
