<?php

namespace App\Application\Inventory\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\UnitOfWork;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class SetMaterialUnitPriceHandler
{
    public function __construct(
        private StockItemRepository $stock,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(SetMaterialUnitPriceCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $item = $this->stock->getById(new EntityId($command->stockItemId));
            $item->material()->changeUnitPrice(
                new Money(number_format((float) $command->unitPrice, 2, '.', ''), $command->currency),
            );
            $this->stock->save($item);
            $this->events->publish($item->pullDomainEvents());
        });
    }
}
