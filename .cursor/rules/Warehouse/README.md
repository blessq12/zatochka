# BC: Склад (Warehouse)

Номенклатура, остатки, ручное списание/приход.

## Агрегаты

- `WarehouseItem` — name, sku, quantity, price
- `StockMovement` — журнал движений (MVP)

## Код

- Domain: `app/Domain/Warehouse/`
- Application: `app/Application/Warehouse/` _(будущее)_

## Правила по слоям

| Файл | Слой | Globs |
|------|------|-------|
| `domain.mdc` | Domain | `app/Domain/Warehouse/**` |
| `application.mdc` | Application | `app/Application/Warehouse/**` |
| `presentation.mdc` | Presentation | Filament + POS read-only |

## ES

- [05-агрегаты — WarehouseItem](../../../es/05-агрегаты/README.md#агрегат-warehouseitem-bc-склад)
