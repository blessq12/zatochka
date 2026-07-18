<?php

namespace App\Application\Inventory\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Inventory\Entity\Material;
use App\Domain\Inventory\Entity\StockItem;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Domain\Inventory\VO\Quantity;
use App\Domain\Inventory\VO\StockCategory;
use App\Domain\Inventory\VO\StockSku;
use App\Domain\Inventory\VO\UnitOfMeasure;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class OpenStockItemHandler
{
    public function __construct(
        private StockItemRepository $stock,
        private DomainEventPublisher $events,
    ) {}

    public function handle(OpenStockItemCommand $command): void
    {
        $unit = UnitOfMeasure::tryFrom($command->unit)
            ?? throw new DomainException('Unknown unit of measure.');
        $category = StockCategory::tryFrom($command->category)
            ?? throw new DomainException('Unknown stock category.');

        $item = StockItem::open(
            new EntityId($command->stockItemId),
            new Material(
                new EntityId($command->materialId),
                StockSku::generate($category, $command->materialId),
                $command->name,
                $unit,
                $category,
                new Money(number_format((float) $command->unitPrice, 2, '.', ''), $command->currency),
            ),
            new Quantity($command->initialQuantity),
        );

        $this->stock->save($item);
        $this->events->publish($item->pullDomainEvents());
    }
}
