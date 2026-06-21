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
| `Enum/` | `StockMovementType` — received, written_off; `WarehouseItemType` — consumable, spare_part |
| `Repository/` | `WarehouseItemRepositoryInterface`, `StockMovementRepositoryInterface` |
| `Event/` | `StockReceived`, `StockWrittenOff` |
| `Exception/` | `WarehousePolicyViolation`, `WarehouseItemNotFoundException` |

### Инварианты

- Остаток ≥ 0 после списания
- Количество прихода/списания > 0

### `WarehouseItemRepositoryInterface`

`findById`, `save`, `search(query, page, perPage)`

## Infrastructure (`app/Infrastructure/Warehouse/`)

Eloquent×2, Mapper×2, Repository×2.

## Application (`app/Application/Warehouse/`)

| Тип | Классы |
|-----|--------|
| Commands | `ReceiveStock`, `WriteOffStock` |
| Query | `SearchWarehouseItems` |
| Presenter | `WarehouseItemPresenter` |

`AddMaterialToOrder` (OrderFulfillment) читает `WarehouseItem`, но **не** списывает автоматически.

## Presentation

| Канал | Путь |
|-------|------|
| Filament | Кластер «Склад»: `ConsumableWarehouseItems`, `SparePartWarehouseItems` — CRUD + приход/списание |
| POS | `GET /api/pos/warehouse/items` — read-only поиск |
| Filament (заказ) | `OrderManageActions::addMaterial()` |

## Данные

`WarehouseSeeder` → `DomainSeeder`: демо расходники и запчасти.

## Тесты

- `tests/Feature/Warehouse/WarehouseStockTest.php`
- `tests/Feature/Warehouse/WarehouseItemTypeTest.php`

## ES

- [WarehouseItem](../../../es/05-агрегаты/README.md#агрегат-warehouseitem-bc-склад)
