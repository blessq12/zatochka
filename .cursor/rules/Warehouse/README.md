# BC: Склад (Warehouse)

Номенклатура и движения (ES). MVP: ручной приход/списание.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ⬜ каркас |
| Presentation | ⬜ |

## Domain (`app/Domain/Warehouse/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `WarehouseItem`, `StockMovement` |
| `Enum/` | `StockMovementType` — received, written_off |
| `Repository/` | `WarehouseItemRepositoryInterface`, `StockMovementRepositoryInterface` |

`StockMovement` хранит `userId`, `orderId` как FK (nullable).

## Infrastructure (`app/Infrastructure/Warehouse/`)

Eloquent×2, Mapper×2, Repository×2. `WarehouseItemModel` → hasMany movements (внутри BC).

## Application / Presentation

Не реализованы. ES: `ReceiveStock`, `WriteOffStock`; POS read-only склад.

## ES

- [WarehouseItem](../../../es/05-агрегаты/README.md#агрегат-warehouseitem-bc-склад)
