<?php

namespace App\Application\Inventory\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Application\Shared\UnitOfWork;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Domain\Inventory\Service\StockMutationService;
use App\Domain\Inventory\VO\Quantity;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class WriteOffMaterialHandler
{
    public function __construct(
        private StockItemRepository $stock,
        private StockMutationService $mutations,
        private EntityIdGenerator $ids,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(WriteOffMaterialCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $movementId = $command->movementId ?? $this->ids->next('warehouse_movement')->value;
            $item = $this->stock->getById(new EntityId($command->stockItemId));

            $unitPrice = null;
            if ($command->unitPrice !== null && trim($command->unitPrice) !== '') {
                $unitPrice = new Money(number_format((float) $command->unitPrice, 2, '.', ''), $command->currency);
            } elseif ($command->orderId !== null) {
                $catalogPrice = $item->material()->unitPrice();
                if ((float) $catalogPrice->amount > 0) {
                    $unitPrice = $catalogPrice;
                }
            }

            $this->mutations->writeOff(
                $item,
                new EntityId($movementId),
                new Quantity($command->quantity),
                $command->comment,
                $command->orderId,
                $command->orderItemId,
                $unitPrice,
            );
            $this->stock->save($item);
            $this->events->publish($item->pullDomainEvents());
        });
    }
}
