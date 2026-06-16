# BC: Исполнение заказа (OrderFulfillment)

**Корень домена:** `Order` (ES). Центральный агрегат.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ⬜ каркас |
| Presentation | ⬜ нет API/Filament |

## Domain (`app/Domain/OrderFulfillment/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `Order`, `OrderWork`, `OrderTool`, `OrderMaterial` |
| `Enum/` | `OrderStatus`, `OrderSource`, `OrderUrgency` |
| `ValueObject/` | `OrderNumber`, `ClientSnapshot` |
| `Repository/` | `OrderRepositoryInterface` |
| `Service/` | `OrderNumberGenerator` |

Поведение на `Order`: `isActive()`, `clientDisplayName()`, `clientDisplayPhone()`. Cross-BC — только ID-поля.

## Infrastructure (`app/Infrastructure/OrderFulfillment/`)

| Папка | Классы |
|-------|--------|
| `Persistence/Eloquent/` | `OrderModel`, `OrderWorkModel`, `OrderToolModel`, `OrderMaterialModel` |
| `Persistence/Mapper/` | `OrderMapper` |
| `Persistence/Repository/` | `EloquentOrderRepository` (save агрегата + sync children) |

`OrderModel`: relations только на works/tools/materials (внутри агрегата).

## Application

`app/Application/OrderFulfillment/{Command,CommandHandler,Query,QueryHandler,Presenter}/` — пусто.

## Presentation

Не реализовано. Целевые каналы по ES: Filament `/cp`, POS `/api/pos/*`.

## ES

- [Order](../../../es/05-агрегаты/README.md#агрегат-order-корень-bc-исполнение-заказа)
- [Команды](../../../es/04-команды/README.md)
