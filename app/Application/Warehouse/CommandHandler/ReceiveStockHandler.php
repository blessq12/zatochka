<?php

namespace App\Application\Warehouse\CommandHandler;

use App\Application\Warehouse\Command\ReceiveStockCommand;
use App\Domain\Warehouse\Entity\StockMovement;
use App\Domain\Warehouse\Enum\StockMovementType;
use App\Domain\Warehouse\Event\StockReceived;
use App\Domain\Warehouse\Exception\WarehouseItemNotFoundException;
use App\Domain\Warehouse\Repository\StockMovementRepositoryInterface;
use App\Domain\Warehouse\Repository\WarehouseItemRepositoryInterface;

final class ReceiveStockHandler
{
    public function __construct(
        private WarehouseItemRepositoryInterface $items,
        private StockMovementRepositoryInterface $movements,
    ) {}

    public function handle(ReceiveStockCommand $command): StockMovement
    {
        $item = $this->items->findById($command->warehouseItemId);

        if ($item === null) {
            throw WarehouseItemNotFoundException::withId($command->warehouseItemId);
        }

        $quantity = number_format((float) $command->quantity, 3, '.', '');
        $updated = $this->items->save($item->receive($quantity));

        $movement = new StockMovement(
            id: null,
            warehouseItemId: $command->warehouseItemId,
            type: StockMovementType::Received,
            quantity: $quantity,
            comment: $command->comment,
            userId: $command->userId,
            orderId: null,
        );

        $saved = $this->movements->save($movement);

        event(new StockReceived($updated, $quantity));

        return $saved;
    }
}
