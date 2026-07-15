<?php

namespace App\Infrastructure\Inventory\Mapper;

use App\Application\Inventory\DTO\StockItemDTO;
use App\Domain\Inventory\Entity\Material;
use App\Domain\Inventory\Entity\StockItem;
use App\Domain\Inventory\Entity\WarehouseMovement;
use App\Domain\Inventory\VO\MovementType;
use App\Domain\Inventory\VO\Quantity;
use App\Infrastructure\Inventory\Model\MaterialModel;
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Inventory\Model\WarehouseMovementModel;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final class StockItemMapper
{
    public function toDomain(StockItemModel $model): StockItem
    {
        $material = new Material(
            new EntityId((int) $model->material->id),
            (string) $model->material->sku,
            (string) $model->material->name,
            (string) $model->material->unit,
        );

        $movements = [];

        foreach ($model->movements as $row) {
            $movements[] = new WarehouseMovement(
                new EntityId((int) $row->id),
                MovementType::from((string) $row->type),
                new Quantity((string) $row->quantity),
                DateTimeImmutable::createFromInterface($row->occurred_at),
                $row->comment !== null ? (string) $row->comment : null,
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
        $model ??= new StockItemModel();
        $model->id = $item->id()->value;
        $model->material_id = $item->material()->id()->value;
        $model->quantity_on_hand = $item->quantityOnHand()->value;

        return $model;
    }

    public function materialToPersistence(StockItem $item): MaterialModel
    {
        $material = $item->material();
        $row = new MaterialModel();
        $row->id = $material->id()->value;
        $row->sku = $material->sku();
        $row->name = $material->name();
        $row->unit = $material->unit();

        return $row;
    }

    /** @return list<WarehouseMovementModel> */
    public function movementsToPersistence(StockItem $item): array
    {
        $rows = [];

        foreach ($item->movements() as $movement) {
            $row = new WarehouseMovementModel();
            $row->id = $movement->id->value;
            $row->stock_item_id = $item->id()->value;
            $row->type = $movement->type->value;
            $row->quantity = $movement->quantity->value;
            $row->comment = $movement->comment;
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
            (string) $model->quantity_on_hand,
        );
    }
}
