# BC: Исполнение заказа (OrderFulfillment)

**Корень домена:** Order. Центральный агрегат системы.

## Агрегаты

- `Order` (корень) — Work, OrderTool, OrderMaterial
- Конвертация `SiteLead` → Order (cross-BC с ClientPortal)

## Код

- Domain: `app/Domain/OrderFulfillment/`
- Application: `app/Application/OrderFulfillment/` _(будущее)_

## Правила по слоям

| Файл | Слой | Globs |
|------|------|-------|
| `domain.mdc` | Domain | `app/Domain/OrderFulfillment/**` |
| `application.mdc` | Application | `app/Application/OrderFulfillment/**` |
| `presentation.mdc` | Presentation | POS + Filament endpoints заказа |

## ES

- [05-агрегаты — Order](../../../es/05-агрегаты/README.md#агрегат-order-корень-bc-исполнение-заказа)
- [04-команды](../../../es/04-команды/README.md)
