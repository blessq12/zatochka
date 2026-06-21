# Документация — ЗАТОЧКА.ТСК

`.cursor/rules/` — единственный источник.

| Файл | Содержание |
|------|------------|
| [flows.mdc](./rules/flows.mdc) | Потоки клиент / мастер / менеджер + cross-BC |
| [contexts.mdc](./rules/contexts.mdc) | BC: процессные vs справочники |
| [domain.mdc](./rules/domain.mdc) | Order, POL, глоссарий |
| [decisions.mdc](./rules/decisions.mdc) | ADR-001…008 |
| [project.mdc](./rules/project.mdc) | Запуск, структура репо |
| [backend/layers.mdc](./rules/backend/layers.mdc) | Слои (alwaysApply) |
| [backend/conventions.mdc](./rules/backend/conventions.mdc) | Именование |

Код: `app/Application/{BC}/` · `routes/api.php` · `PersistenceServiceProvider.php`
