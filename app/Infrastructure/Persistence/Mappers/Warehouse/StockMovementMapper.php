<?php

namespace App\Infrastructure\Persistence\Mappers\Warehouse;

use App\Domain\Warehouse\Entities\StockMovement;
use App\Infrastructure\Persistence\Eloquent\Models\Warehouse\StockMovementModel;

final class StockMovementMapper
{
    public function toDomain(StockMovementModel $model): StockMovement
    {
        return new StockMovement(
            id: $model->id,
            warehouseItemId: $model->warehouse_item_id,
            type: $model->type,
            quantity: (string) $model->quantity,
            comment: $model->comment,
            userId: $model->user_id,
            orderId: $model->order_id,
        );
    }

    public function fillModel(StockMovement $movement, StockMovementModel $model): void
    {
        $model->fill([
            'warehouse_item_id' => $movement->warehouseItemId(),
            'type' => $movement->type(),
            'quantity' => $movement->quantity(),
            'comment' => $movement->comment(),
            'user_id' => $movement->userId(),
            'order_id' => $movement->orderId(),
        ]);
    }
}
