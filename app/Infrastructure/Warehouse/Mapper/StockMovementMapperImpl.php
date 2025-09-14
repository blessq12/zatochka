<?php

namespace App\Infrastructure\Warehouse\Mapper;

use App\Domain\Warehouse\Entity\StockMovement;
use App\Domain\Warehouse\Mapper\StockMovementMapper;
use App\Models\StockMovement as StockMovementModel;

class StockMovementMapperImpl implements StockMovementMapper
{
    public function toDomain($eloquentModel): StockMovement
    {
        return new StockMovement(
            id: $eloquentModel->id,
            stockItemId: $eloquentModel->stock_item_id,
            movementType: $eloquentModel->movement_type,
            quantity: $eloquentModel->quantity,
            previousQuantity: $eloquentModel->previous_quantity,
            newQuantity: $eloquentModel->new_quantity,
            reason: $eloquentModel->reason,
            orderId: $eloquentModel->order_id,
            userId: $eloquentModel->user_id,
            unitPrice: $eloquentModel->unit_price,
            reference: $eloquentModel->reference,
            createdAt: $eloquentModel->created_at ? $eloquentModel->created_at->toDateTime() : null,
        );
    }

    public function toEloquent(StockMovement $domainEntity): array
    {
        return [
            'stock_item_id' => $domainEntity->getStockItemId(),
            'movement_type' => $domainEntity->getMovementType(),
            'quantity' => $domainEntity->getQuantity(),
            'previous_quantity' => $domainEntity->getPreviousQuantity(),
            'new_quantity' => $domainEntity->getNewQuantity(),
            'reason' => $domainEntity->getReason(),
            'order_id' => $domainEntity->getOrderId(),
            'user_id' => $domainEntity->getUserId(),
            'unit_price' => $domainEntity->getUnitPrice(),
            'reference' => $domainEntity->getReference(),
        ];
    }

    public function toEloquentModel(StockMovement $domainEntity): StockMovementModel
    {
        $model = new StockMovementModel();
        $model->fill($this->toEloquent($domainEntity));

        if ($domainEntity->getId()) {
            $model->id = $domainEntity->getId();
        }

        return $model;
    }
}
