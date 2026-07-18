<?php

namespace App\Application\Inventory\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Application\Shared\UnitOfWork;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Domain\Inventory\Service\StockMutationService;
use App\Shared\ValueObject\EntityId;

final readonly class ReverseOrderMaterialWriteOffHandler
{
    public function __construct(
        private StockItemRepository $stock,
        private StockMutationService $mutations,
        private EntityIdGenerator $ids,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(ReverseOrderMaterialWriteOffCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $item = $this->stock->getById(new EntityId($command->stockItemId));
            $reversalId = $command->reversalMovementId ?? $this->ids->next('warehouse_movement')->value;

            $this->mutations->reverseWriteOff(
                $item,
                new EntityId($reversalId),
                new EntityId($command->writeOffMovementId),
                $command->comment,
            );
            $this->stock->save($item);
            $this->events->publish($item->pullDomainEvents());
        });
    }
}
