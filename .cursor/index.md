# Индекс правил Cursor — бэкенд

Монолит Laravel, 6 bounded contexts, **DDD + Hexagonal**. Фронт в scope правил **не входит**.

## Слои

| Слой | Путь | Содержимое |
|------|------|------------|
| Domain | `app/Domain/{BC}/` | Entities, Enums, ValueObjects, Repositories (interfaces), Services |
| Application | `app/Application/{BC}/` | Commands, Handlers |
| Infrastructure | `app/Infrastructure/` | Eloquent Models, Mappers, Repository adapters |
| Presentation | `app/Http/`, `app/Filament/` | Controllers, Resources |

## Bounded contexts

| BC | Domain | Infrastructure Models |
|----|--------|----------------------|
| Исполнение заказа | [OrderFulfillment](./rules/OrderFulfillment/) | `Infrastructure/.../OrderFulfillment/` |
| Клиентский портал | [ClientPortal](./rules/ClientPortal/) | `Infrastructure/.../ClientPortal/` |
| Оборудование | [Equipment](./rules/Equipment/) | `Infrastructure/.../Equipment/` |
| Склад | [Warehouse](./rules/Warehouse/) | `Infrastructure/.../Warehouse/` |
| Справочники | [Catalog](./rules/Catalog/) | `Infrastructure/.../Catalog/` |
| Идентичность | [Identity](./rules/Identity/) | `Infrastructure/.../Identity/UserModel` |

## Источник истины

- Домен: `es/`
- DI портов: `app/Infrastructure/Providers/PersistenceServiceProvider.php`
