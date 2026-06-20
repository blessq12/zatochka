# BC: Оборудование (Equipment)

Реестр оборудования (ES). История ремонтов — query по `Order.equipment_id`.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ✅ |
| Presentation | ✅ Filament, POS |

## Domain (`app/Domain/Equipment/`)

| Папка | Содержание |
|-------|------------|
| `Entity/` | `Equipment` — `register()`, name, brand, model, serialNumbers[] |
| `Repository/` | `EquipmentRepositoryInterface` |
| `Event/` | `EquipmentRegistered` |
| `Exception/` | `EquipmentNotFoundException` |

### `EquipmentRepositoryInterface`

`findById`, `save`, `findBySerialNumber`, `search(query, page, perPage)`

## Infrastructure (`app/Infrastructure/Equipment/`)

`EquipmentModel` (json `serial_numbers`), `EquipmentMapper`, `EloquentEquipmentRepository`

## Application (`app/Application/Equipment/`)

| Тип | Классы |
|-----|--------|
| Command | `RegisterEquipment` → `EquipmentRegistered` |
| Query | `SearchEquipment`, `GetEquipmentOrderHistory` |
| Presenter | `EquipmentPresenter` |

`LinkEquipmentToOrder` — в BC OrderFulfillment.

## Presentation

| Канал | Путь |
|-------|------|
| Filament | `Equipment/EquipmentResource` — список, создание |
| Filament (заказ) | действие «Привязать оборудование» на `ViewOrder` |
| POS | `GET /api/pos/equipment`, `GET /api/pos/equipment/{id}/orders` |

## Тесты

`tests/Feature/Equipment/EquipmentTest.php`

## ES

- [Equipment](../../../es/05-агрегаты/README.md#агрегат-equipment-bc-оборудование)
