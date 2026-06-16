# Общие правила бэкенда

Cross-cutting. Отражают **фактическую** структуру репозитория.

## Файлы правил

| Файл | Содержание |
|------|------------|
| `layers.mdc` | Дерево слоёв, зависимости (alwaysApply) |
| `cross-bc.mdc` | Границы BC в текущем коде |
| `conventions.mdc` | Язык, именование команд/событий (целевые) |

## Что реализовано глобально

- Hexagonal: Domain без Laravel; Infrastructure — Eloquent-адаптеры
- 12 repository ports → bindings в `PersistenceServiceProvider`
- Миграции + `DomainSeeder` для Catalog, Identity (демо-мастер), ClientPortal-таблицы пустые
- Auth: `User` (web/sanctum), `ClientAuthModel` (guard `client`) — см. `config/auth.php`

## Чего нет в коде

- Application handlers (Command/Query)
- Domain events, policies
- Filament, POS API, публичный REST по ES
- Тесты домена / use cases

## ES и ADR

- [es/05-агрегаты](../../../es/05-агрегаты/README.md)
- [es/09-решения](../../../es/09-решения/README.md)
