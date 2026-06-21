# Индекс правил Cursor — бэкенд

DDD + Hexagonal, BC-first. Описание **текущего кода**, не roadmap.

## Статус слоёв (сейчас)

| Слой | Статус |
|------|--------|
| `app/Shared/ValueObject/` | ✅ EntityId, Phone, Email, Money (VO-заглушки, в домене пока не используются) |
| `app/Domain/{BC}/` | ✅ Entity, Enum, Repository, Event, Exception; поведение агрегатов |
| `app/Infrastructure/{BC}/` | ✅ Persistence, Auth, DomPDF (OrderFulfillment) |
| `app/Application/{BC}/` | ✅ use cases по всем BC (Identity — Register/Update master) |
| Presentation | ✅ публичный `/api/*`, POS `/api/pos/*` + Vue `/pos`, Filament `/cp` (7 кластеров) |

**Тесты:** 25 (Unit + Feature). `php artisan test`.

## BC

| BC | README | Application | Presentation |
|----|--------|-------------|--------------|
| OrderFulfillment | [rules/OrderFulfillment](./rules/OrderFulfillment/) | lifecycle, цены, PDF, POS | Filament «Заказы», POS, PDF |
| ClientPortal | [rules/ClientPortal](./rules/ClientPortal/) | ЛК, заявки, отзывы | `/api/leads`, `/api/auth`, `/api/client`, Vue `/client/dashboard`, Filament «Клиенты» |
| POS (вертикаль) | [rules/POS](./rules/POS/) | — (OrderFulfillment + Equipment + Warehouse + Identity) | `/api/pos/*`, Vue `/pos` |
| Company | [rules/Company](./rules/Company/) | филиал, контент сайта | Filament «Компания» |
| Pricing | [rules/Pricing](./rules/Pricing/) | прайс-лист | Filament «Прайс-лист» |
| PublicSite (фасад) | [rules/PublicSite](./rules/PublicSite/) | `GetPublicBootstrap` | `GET /api/bootstrap`, Vue `bootstrapStore` |
| Equipment | [rules/Equipment](./rules/Equipment/) | register, search, history | Filament «Оборудование», POS |
| Warehouse | [rules/Warehouse](./rules/Warehouse/) | приход/списание, search | Filament «Склад», POS read-only |
| Identity | [rules/Identity](./rules/Identity/) | Register/Update master | POS login, Filament «Идентичность» |

## Filament-кластеры `/cp`

| Кластер | Ресурсы |
|---------|---------|
| Заказы | SiteLeads, Orders |
| Клиенты | Clients, Reviews |
| Оборудование | Equipment |
| Склад | ConsumableWarehouseItems, SparePartWarehouseItems |
| Компания | SiteContent, Branches |
| Прайс-лист | SharpeningPriceItems, RepairPriceItems |
| Идентичность | Masters, Managers |

## Общие правила

- [backend/](./rules/backend/) — слои, cross-BC, конвенции

## Источники

- Бизнес-правила (ES): `es/`
- Use cases: `app/Application/README.md`
- DI портов: `app/Infrastructure/Shared/Provider/PersistenceServiceProvider.php`
- Сидер: `database/seeders/DomainSeeder.php` (Company → Pricing → Identity → …)
- Публичный контент SPA: `GET /api/bootstrap` → `resources/js/stores/bootstrapStore.js`
- Исключения API: `bootstrap/app.php`
