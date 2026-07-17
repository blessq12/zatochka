<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Workshop\Port\OrderProductionContextPort;
use App\Application\Workshop\WorkAttachment\WorkAttachmentStrategyResolver;
use App\Domain\Workshop\Entity\PerformedWork;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\ValueObject\EntityId;

final readonly class AddMasterWorkHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private OrderProductionContextPort $orders,
        private WorkAttachmentStrategyResolver $workAttachment,
        private DomainEventPublisher $events,
    ) {}

    public function handle(AddMasterWorkCommand $command): void
    {
        $task = $this->tasks->getById(new EntityId($command->productionTaskId));
        $order = $this->orders->getById($task->orderId());
        $target = $this->workAttachment
            ->for($order)
            ->resolveTarget($order, $command->orderItemId, $command->equipmentComponentId);

        $task->addWork(new PerformedWork(
            new EntityId($command->workId),
            $target->orderItemId,
            new EntityId($command->masterId),
            $command->text,
            $target->equipmentComponentId,
        ));
        $this->tasks->save($task);
        $this->events->publish($task->pullDomainEvents());
    }
}
