<?php

namespace App\Application\Inventory\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Application\Shared\UnitOfWork;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Domain\Inventory\Service\StockMutationService;
use App\Domain\Inventory\VO\Quantity;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class ReplaceOrderMaterialWriteOffHandler
{
    public function __construct(
        private StockItemRepository $stock,
        private StockMutationService $mutations,
        private EntityIdGenerator $ids,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(ReplaceOrderMaterialWriteOffCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $item = $this->stock->getById(new EntityId($command->stockItemId));
            $original = $item->findMovement(new EntityId($command->writeOffMovementId));

            if ($original->orderId === null) {
                throw new DomainException('Only order-linked write-offs can be replaced.');
            }

            $orderId = $original->orderId;
            $orderItemId = $command->orderItemId ?? $original->orderItemId;

            $reversalId = $command->reversalMovementId ?? $this->ids->next('warehouse_movement')->value;
            $newWriteOffId = $command->newWriteOffMovementId ?? $this->ids->next('warehouse_movement')->value;

            $this->mutations->reverseWriteOff(
                $item,
                new EntityId($reversalId),
                new EntityId($command->writeOffMovementId),
                $command->comment,
            );

            $this->mutations->writeOff(
                $item,
                new EntityId($newWriteOffId),
                new Quantity($command->quantity),
                $command->comment,
                $orderId,
                $orderItemId,
                new Money(number_format((float) $command->unitPrice, 2, '.', ''), $command->currency),
            );

            $this->stock->save($item);
            $this->events->publish($item->pullDomainEvents());
        });
    }
}
