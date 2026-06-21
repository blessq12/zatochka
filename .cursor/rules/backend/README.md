# Общие правила бэкенда

Cross-cutting. Отражают **фактическую** структуру репозитория.

## Файлы правил

| Файл | Содержание |
|------|------------|
| `layers.mdc` | Дерево слоёв, зависимости (alwaysApply) |
| `cross-bc.mdc` | Границы BC, orchestration в Application |
| `conventions.mdc` | Язык, именование Command/Event |

## Что реализовано глобально

- Hexagonal: Domain без Laravel; Infrastructure — Eloquent-адаптеры
- Repository ports → bindings в `PersistenceServiceProvider` (+ `PdfRendererInterface`)
- Domain events — Laravel `event()`, синхронно
- Миграции + `DomainSeeder` (Company → Pricing → Identity → Warehouse → Equipment → ClientPortal → DemoOrder)
- Auth: `User` (web/sanctum/POS), `ClientAuthModel` (guard `client`)
- Exception → JSON mapping в `bootstrap/app.php` для `api/*`
- Filament 4, панель `/cp`, 7 кластеров
- DomPDF — PDF on-the-fly, без хранения файлов

## MVP закрыт (фазы 0–5)

| Фаза | Содержание |
|------|------------|
| 0 | Поведение Order, инварианты, unit-тесты |
| 1 | Lifecycle, POS, Filament заказы |
| 2 | Цены, материалы, internal notes |
| 3 | ClientPortal: leads, ЛК, отзывы |
| 4 | Bootstrap, склад, оборудование |
| 5 | PDF (`GenerateDocument`), POS read models |

## ES и ADR

- [es/05-агрегаты](../../../es/05-агрегаты/README.md)
- [es/07-read-models](../../../es/07-read-models/README.md)
- [es/09-решения](../../../es/09-решения/README.md)
