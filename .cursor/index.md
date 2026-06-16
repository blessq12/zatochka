# Индекс правил Cursor — бэкенд

DDD + Hexagonal, **BC-first на каждом слое**.

## Слои

| Слой | Путь |
|------|------|
| Shared Kernel | `app/Shared/ValueObject/` |
| Domain | `app/Domain/{BC}/Entity|ValueObject|Enum|Repository|Service/` |
| Application | `app/Application/{BC}/Command|CommandHandler|Query|QueryHandler|Presenter/` |
| Infrastructure | `app/Infrastructure/{BC}/Persistence|Auth/` |
| DI | `app/Infrastructure/Shared/Provider/` |

## BC

OrderFulfillment · ClientPortal · Catalog · Equipment · Warehouse · Identity

## Документация

- Домен: `es/`
- DI: `PersistenceServiceProvider.php`
