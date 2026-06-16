# Индекс правил Cursor — бэкенд

DDD + Hexagonal, BC-first. Описание **текущего кода**, не roadmap.

## Статус слоёв (сейчас)

| Слой | Статус |
|------|--------|
| `app/Shared/ValueObject/` | ✅ EntityId, Phone, Email, Money (VO-заглушки, в домене пока не используются) |
| `app/Domain/{BC}/` | ✅ Entity, Enum, Repository (ports), Service/ValueObject где нужно; поведение Order |
| `app/Infrastructure/{BC}/` | ✅ Persistence (Eloquent, Mapper, Repository adapters), Auth где нужно |
| `app/Application/{BC}/` | ⬜ каркас папок (`.gitkeep`), **классов нет** |
| `app/Http/`, Filament | ⬜ SPA catch-all; API/Filament по BC **не реализованы** |

## BC

| BC | README | Domain | Infrastructure |
|----|--------|--------|----------------|
| OrderFulfillment | [rules/OrderFulfillment](./rules/OrderFulfillment/) | 10 классов | Eloquent×4, Mapper, Repository |
| ClientPortal | [rules/ClientPortal](./rules/ClientPortal/) | 7 классов | Eloquent×3, Auth, Mapper×3, Repository×3 |
| Catalog | [rules/Catalog](./rules/Catalog/) | 8 классов | Eloquent×4, Mapper×4, Repository×4 |
| Equipment | [rules/Equipment](./rules/Equipment/) | 2 класса | Eloquent, Mapper, Repository |
| Warehouse | [rules/Warehouse](./rules/Warehouse/) | 4 класса | Eloquent×2, Mapper×2, Repository×2 |
| Identity | [rules/Identity](./rules/Identity/) | 2 класса | UserModel, Mapper, Repository |

## Общие правила

- [backend/](./rules/backend/) — слои, cross-BC, конвенции

## Источники

- Бизнес-правила (ES): `es/`
- DI портов: `app/Infrastructure/Shared/Provider/PersistenceServiceProvider.php`
- Сидер: `database/seeders/DomainSeeder.php` (пишет через Eloquent models)
