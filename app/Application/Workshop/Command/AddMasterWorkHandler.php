<?php

namespace App\Application\Workshop\Command;

use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class AddMasterWorkHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private OrderRepository $orders,
        private AddMasterCommentHandler $addComment,
    ) {}

    public function handle(AddMasterWorkCommand $command): void
    {
        $task = $this->tasks->getById(new EntityId($command->productionTaskId));
        $order = $this->orders->getById($task->orderId());

        $orderItem = null;

        foreach ($order->items() as $item) {
            if ($item->id()->value === $command->orderItemId) {
                $orderItem = $item;

                break;
            }
        }

        if ($orderItem === null) {
            throw new DomainException('Work must be linked to an order item.');
        }

        $this->addComment->handle(new AddMasterCommentCommand(
            $command->productionTaskId,
            $command->workId,
            $command->masterId,
            $command->text,
            $command->orderItemId,
        ));
    }
}
