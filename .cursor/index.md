# Индекс правил Cursor — бэкенд

DDD + Hexagonal, BC-first. Описание **текущего кода**, не roadmap.

## Статус слоёв (сейчас)

| Слой | Статус |
|------|--------|
| `app/Shared/ValueObject/` | ✅ EntityId, Phone, Email, Money (VO-заглушки, в домене пока не используются) |
| `app/Domain/{BC}/` | ✅ Entity, Enum, Repository, Event, Exception; поведение агрегатов |
| `app/Infrastructure/{BC}/` | ✅ Persistence, Auth, DomPDF (OrderFulfillment) |
| `app/Application/{BC}/` | ✅ use cases по всем BC, кроме Identity (каркас) |
| Presentation | ✅ публичный `/api/*`, POS `/api/pos/*`, Filament `/cp` |

**Тесты:** 23 (Unit + Feature). `php artisan test`.

## BC

| BC | README | Application | Presentation |
|----|--------|-------------|--------------|
| OrderFulfillment | [rules/OrderFulfillment](./rules/OrderFulfillment/) | lifecycle, цены, PDF, POS | Filament заказы, POS, PDF |
| ClientPortal | [rules/ClientPortal](./rules/ClientPortal/) | ЛК, заявки, отзывы | `/api/leads`, `/api/auth`, `/api/client`, Filament |
| Catalog | [rules/Catalog](./rules/Catalog/) | `GetPublicBootstrap` | `GET /api/bootstrap` |
| Equipment | [rules/Equipment](./rules/Equipment/) | register, search, history | Filament, POS |
| Warehouse | [rules/Warehouse](./rules/Warehouse/) | приход/списание, search | Filament, POS read-only |
| Identity | [rules/Identity](./rules/Identity/) | ⬜ каркас | POS login, Filament web auth |

## Общие правила

- [backend/](./rules/backend/) — слои, cross-BC, конвенции

## Источники

- Бизнес-правила (ES): `es/`
- Use cases: `app/Application/README.md`
- DI портов: `app/Infrastructure/Shared/Provider/PersistenceServiceProvider.php`
- Сидер: `database/seeders/DomainSeeder.php`
- Исключения API: `bootstrap/app.php`
