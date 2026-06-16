<?php

namespace App\Application\Warehouse\CommandHandler;

use App\Application\Warehouse\Command\WriteOffStockCommand;
use App\Domain\Warehouse\Entity\StockMovement;
use App\Domain\Warehouse\Enum\StockMovementType;
use App\Domain\Warehouse\Event\StockWrittenOff;
use App\Domain\Warehouse\Exception\WarehouseItemNotFoundException;
use App\Domain\Warehouse\Repository\StockMovementRepositoryInterface;
use App\Domain\Warehouse\Repository\WarehouseItemRepositoryInterface;

final class WriteOffStockHandler
{
    public function __construct(
        private WarehouseItemRepositoryInterface $items,
        private StockMovementRepositoryInterface $movements,
    ) {}

    public function handle(WriteOffStockCommand $command): StockMovement
    {
        $item = $this->items->findById($command->warehouseItemId);

        if ($item === null) {
            throw WarehouseItemNotFoundException::withId($command->warehouseItemId);
        }

        $quantity = number_format((float) $command->quantity, 3, '.', '');
        $updated = $this->items->save($item->writeOff($quantity));

        $movement = new StockMovement(
            id: null,
            warehouseItemId: $command->warehouseItemId,
            type: StockMovementType::WrittenOff,
            quantity: $quantity,
            comment: $command->comment,
            userId: $command->userId,
            orderId: null,
        );

        $saved = $this->movements->save($movement);

        event(new StockWrittenOff($updated, $quantity));

        return $saved;
    }
}
