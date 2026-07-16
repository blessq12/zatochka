<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Domain\Workshop\Service\MasterQueueService;
use App\Shared\ValueObject\EntityId;

final readonly class OpenProductionTaskHandler
{
    public function __construct(
        private MasterQueueService $queue,
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
        private EntityIdGenerator $ids,
    ) {}

    public function handle(OpenProductionTaskCommand $command): void
    {
        $orderId = new OrderId($command->orderId);

        if ($this->tasks->findByOrderId($orderId) !== null) {
            return;
        }

        $productionTaskId = $command->productionTaskId
            ?? $this->ids->next('production_task')->value;

        $task = $this->queue->openTask(
            new EntityId($productionTaskId),
            $orderId,
        );

        $this->tasks->save($task);
        $this->events->publish($task->pullDomainEvents());
    }
}
