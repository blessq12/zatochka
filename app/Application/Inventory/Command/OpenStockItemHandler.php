<?php

namespace App\Application\Inventory\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Inventory\Entity\Material;
use App\Domain\Inventory\Entity\StockItem;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Domain\Inventory\VO\Quantity;
use App\Shared\ValueObject\EntityId;

final readonly class OpenStockItemHandler
{
    public function __construct(
        private StockItemRepository $stock,
        private DomainEventPublisher $events,
    ) {}

    public function handle(OpenStockItemCommand $command): void
    {
        $item = StockItem::open(
            new EntityId($command->stockItemId),
            new Material(
                new EntityId($command->materialId),
                $command->sku,
                $command->name,
                $command->unit,
            ),
            new Quantity($command->initialQuantity),
        );

        $this->stock->save($item);
        $this->events->publish($item->pullDomainEvents());
    }
}
