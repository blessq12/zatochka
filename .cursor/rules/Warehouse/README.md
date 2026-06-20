# BC: Склад (Warehouse)

Номенклатура и движения (ES). MVP: ручной приход/списание.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ✅ |
| Presentation | ✅ Filament, POS read-only |

## Domain (`app/Domain/Warehouse/`)

| Папка | Содержание |
|-------|------------|
| `Entity/` | `WarehouseItem` — `create()`, `receive()`, `writeOff()`; `StockMovement` |
| `Enum/` | `StockMovementType` — received, written_off |
| `Repository/` | `WarehouseItemRepositoryInterface`, `StockMovementRepositoryInterface` |
| `Event/` | `StockReceived`, `StockWrittenOff` |
| `Exception/` | `WarehousePolicyViolation`, `WarehouseItemNotFoundException` |

### Инварианты

- Остаток ≥ 0 после списания
- Количество прихода/списания > 0

### `WarehouseItemRepositoryInterface`

`findById`, `save`, `search(query, page, perPage)`

`StockMovement` хранит `userId`, `orderId` как FK (nullable).

## Infrastructure (`app/Infrastructure/Warehouse/`)

Eloquent×2, Mapper×2, Repository×2. `WarehouseItemModel` → hasMany movements.

## Application (`app/Application/Warehouse/`)

| Тип | Классы |
|-----|--------|
| Commands | `ReceiveStock`, `WriteOffStock` |
| Query | `SearchWarehouseItems` |
| Presenter | `WarehouseItemPresenter` |

`AddMaterialToOrder` (OrderFulfillment) читает `WarehouseItem`, но **не** списывает автоматически (ES: MVP вручную).

## Presentation

| Канал | Путь |
|-------|------|
| Filament | `WarehouseItems/WarehouseItemResource` — приход/списание по строке |
| POS | `GET /api/pos/warehouse/items` — read-only поиск |
| Filament (заказ) | добавление материала в заказ |

## Данные

`DomainSeeder`: `DEMO-001` — демо-запчасть, qty 10.

## Тесты

`tests/Feature/Warehouse/WarehouseStockTest.php`

## ES

- [WarehouseItem](../../../es/05-агрегаты/README.md#агрегат-warehouseitem-bc-склад)
