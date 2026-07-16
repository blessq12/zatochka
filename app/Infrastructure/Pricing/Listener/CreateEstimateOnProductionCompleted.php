<?php

namespace App\Infrastructure\Pricing\Listener;

use App\Application\Pricing\Command\CreateEstimateCommand;
use App\Application\Pricing\Command\CreateEstimateHandler;
use App\Application\Pricing\ReadPort\EstimateReadPort;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Workshop\Event\ProductionCompleted;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;

final readonly class CreateEstimateOnProductionCompleted
{
    public function __construct(
        private OrderRepository $orders,
        private EstimateReadPort $estimates,
        private CreateEstimateHandler $createEstimate,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function handle(ProductionCompleted $event): void
    {
        $order = $this->orders->getById($event->orderId);

        foreach ($order->items() as $item) {
            if ($item->isFullyRejected()) {
                continue;
            }

            if ($this->estimates->findByOrderItemId($item->id()->value) !== null) {
                continue;
            }

            $this->createEstimate->handle(new CreateEstimateCommand(
                $this->ids->next('estimate')->value,
                $item->id()->value,
                '0.00',
            ));
        }
    }
}
