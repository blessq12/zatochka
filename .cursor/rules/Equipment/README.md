# BC: Оборудование (Equipment)

Реестр оборудования (ES). История ремонтов — query по `Order.equipment_id` (не реализована).

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ⬜ каркас |
| Presentation | ⬜ |

## Domain (`app/Domain/Equipment/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `Equipment` — name, brand, model, serialNumbers[] |
| `Repository/` | `EquipmentRepositoryInterface` |

## Infrastructure (`app/Infrastructure/Equipment/`)

`EquipmentModel`, `EquipmentMapper`, `EloquentEquipmentRepository`

## Application / Presentation

Не реализованы. ES: `RegisterEquipment`, `LinkEquipmentToOrder`, поиск в POS.

## ES

- [Equipment](../../../es/05-агрегаты/README.md#агрегат-equipment-bc-оборудование)
