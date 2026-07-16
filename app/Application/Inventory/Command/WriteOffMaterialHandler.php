<?php

namespace App\Application\Inventory\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Domain\Inventory\Service\StockMutationService;
use App\Domain\Inventory\VO\Quantity;
use App\Shared\ValueObject\EntityId;

final readonly class WriteOffMaterialHandler
{
    public function __construct(
        private StockItemRepository $stock,
        private StockMutationService $mutations,
        private EntityIdGenerator $ids,
        private DomainEventPublisher $events,
    ) {}

    public function handle(WriteOffMaterialCommand $command): void
    {
        $movementId = $command->movementId ?? $this->ids->next('warehouse_movement')->value;
        $item = $this->stock->getById(new EntityId($command->stockItemId));
        $this->mutations->writeOff(
            $item,
            new EntityId($movementId),
            new Quantity($command->quantity),
            $command->comment,
            $command->orderId,
            $command->orderItemId,
        );
        $this->stock->save($item);
        $this->events->publish($item->pullDomainEvents());
    }
}
