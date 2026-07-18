<?php

namespace App\Infrastructure\Inventory\Mapper;

use App\Application\Inventory\DTO\StockItemDTO;
use App\Domain\Inventory\Entity\Material;
use App\Domain\Inventory\Entity\StockItem;
use App\Domain\Inventory\Entity\WarehouseMovement;
use App\Domain\Inventory\VO\MovementType;
use App\Domain\Inventory\VO\Quantity;
use App\Domain\Inventory\VO\StockCategory;
use App\Domain\Inventory\VO\StockSku;
use App\Domain\Inventory\VO\UnitOfMeasure;
use App\Infrastructure\Inventory\Model\MaterialModel;
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Inventory\Model\WarehouseMovementModel;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final class StockItemMapper
{
    public function toDomain(StockItemModel $model): StockItem
    {
        $material = new Material(
            new EntityId((int) $model->material->id),
            new StockSku((string) $model->material->sku),
            (string) $model->material->name,
            UnitOfMeasure::from((string) $model->material->unit),
            StockCategory::from((string) $model->material->category),
            new Money(
                (string) ($model->material->unit_price ?? '0.00'),
                (string) ($model->material->currency ?: 'RUB'),
            ),
        );

        $movements = [];

        foreach ($model->movements as $row) {
            $unitPrice = null;
            if ($row->unit_price !== null && $row->unit_price !== '') {
                $unitPrice = new Money(
                    (string) $row->unit_price,
                    (string) ($row->currency ?: 'RUB'),
                );
            }

            $movements[] = new WarehouseMovement(
                new EntityId((int) $row->id),
                MovementType::from((string) $row->type),
                new Quantity((string) $row->quantity),
                DateTimeImmutable::createFromInterface($row->occurred_at),
                $row->comment !== null ? (string) $row->comment : null,
                $row->order_id !== null ? (string) $row->order_id : null,
                $row->order_item_id !== null ? (int) $row->order_item_id : null,
                $unitPrice,
                $row->reverses_movement_id !== null
                    ? new EntityId((int) $row->reverses_movement_id)
                    : null,
            );
        }

        return StockItem::reconstitute(
            new EntityId((int) $model->id),
            $material,
            new Quantity((string) $model->quantity_on_hand),
            $movements,
        );
    }

    public function toPersistence(StockItem $item, ?StockItemModel $model = null): StockItemModel
    {
        $model ??= new StockItemModel;
        $model->id = $item->id()->value;
        $model->material_id = $item->material()->id()->value;
        $model->quantity_on_hand = $item->quantityOnHand()->value;

        return $model;
    }

    public function materialToPersistence(StockItem $item): MaterialModel
    {
        $material = $item->material();
        $row = new MaterialModel;
        $row->id = $material->id()->value;
        $row->sku = $material->sku()->value;
        $row->name = $material->name();
        $row->unit = $material->unit()->value;
        $row->category = $material->category()->value;
        $row->unit_price = $material->unitPrice()->amount;
        $row->currency = $material->unitPrice()->currency;

        return $row;
    }

    /** @return list<WarehouseMovementModel> */
    public function movementsToPersistence(StockItem $item): array
    {
        $rows = [];

        foreach ($item->movements() as $movement) {
            $row = new WarehouseMovementModel;
            $row->id = $movement->id->value;
            $row->stock_item_id = $item->id()->value;
            $row->type = $movement->type->value;
            $row->quantity = $movement->quantity->value;
            $row->unit_price = $movement->unitPrice?->amount;
            $row->currency = $movement->unitPrice?->currency;
            $row->comment = $movement->comment;
            $row->order_id = $movement->orderId;
            $row->order_item_id = $movement->orderItemId;
            $row->reverses_movement_id = $movement->reversesMovementId?->value;
            $row->occurred_at = $movement->occurredAt;
            $rows[] = $row;
        }

        return $rows;
    }

    public function toDTO(StockItemModel $model): StockItemDTO
    {
        return new StockItemDTO(
            (int) $model->id,
            (int) $model->material->id,
            (string) $model->material->sku,
            (string) $model->material->name,
            (string) $model->material->unit,
            (string) $model->material->category,
            (string) $model->quantity_on_hand,
            (string) ($model->material->unit_price ?? '0.00'),
            (string) ($model->material->currency ?: 'RUB'),
        );
    }
}
