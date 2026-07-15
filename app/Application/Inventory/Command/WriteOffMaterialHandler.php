<?php

namespace App\Application\Inventory\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Domain\Inventory\Service\StockMutationService;
use App\Domain\Inventory\VO\Quantity;
use App\Shared\ValueObject\EntityId;

final readonly class WriteOffMaterialHandler
{
    public function __construct(
        private StockItemRepository $stock,
        private StockMutationService $mutations,
        private DomainEventPublisher $events,
    ) {}

    public function handle(WriteOffMaterialCommand $command): void
    {
        $item = $this->stock->getById(new EntityId($command->stockItemId));
        $this->mutations->writeOff(
            $item,
            new EntityId($command->movementId),
            new Quantity($command->quantity),
            $command->comment,
        );
        $this->stock->save($item);
        $this->events->publish($item->pullDomainEvents());
    }
}
