<?php

namespace App\Application\Workshop\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Application\Shared\UnitOfWork;
use App\Application\Workshop\Port\OrderProductionContextPort;
use App\Application\Workshop\WorkAttachment\WorkAttachmentStrategyResolver;
use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Entity\PerformedWork;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class SyncOrderPerformedWorksHandler
{
    public function __construct(
        private ProductionTaskRepository $tasks,
        private OrderProductionContextPort $orders,
        private WorkAttachmentStrategyResolver $workAttachment,
        private EntityIdGenerator $ids,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(SyncOrderPerformedWorksCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $task = $this->tasks->findByOrderId(new OrderId($command->orderId))
                ?? throw new DomainException('Production task not found for order.');

            $masterId = $task->masterId()
                ?? throw new DomainException('Cannot sync performed works without an assigned master.');

            $context = $this->orders->getById($task->orderId()->value);
            $attachment = $this->workAttachment->for($context);

            $desiredIds = [];
            foreach ($command->works as $item) {
                if ($item->workId !== null) {
                    $desiredIds[$item->workId] = true;
                }
            }

            foreach ([...$task->works()] as $existing) {
                if (! isset($desiredIds[$existing->id->value])) {
                    $task->managerRemoveWork($existing->id);
                }
            }

            foreach ($command->works as $item) {
                $text = trim($item->text);
                if ($text === '') {
                    throw new DomainException('Performed work description cannot be empty.');
                }

                if ($item->workId !== null) {
                    $current = null;
                    foreach ($task->works() as $work) {
                        if ($work->id->value === $item->workId) {
                            $current = $work;
                            break;
                        }
                    }

                    if ($current === null) {
                        throw new DomainException(sprintf('Performed work %d not found.', $item->workId));
                    }

                    if ($current->description !== $text) {
                        $task->changeWorkDescription($current->id, $text);
                    }

                    continue;
                }

                $target = $attachment->resolveTarget(
                    $context,
                    $item->orderItemId,
                    $item->equipmentComponentId,
                );

                $task->managerAddWork(new PerformedWork(
                    new EntityId($this->ids->next('performed_work')->value),
                    $target->orderItemId,
                    $masterId,
                    $text,
                    $target->equipmentComponentId,
                ));
            }

            $this->tasks->save($task);
            $this->events->publish($task->pullDomainEvents());
        });
    }
}
