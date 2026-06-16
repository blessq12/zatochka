# BC: Идентичность (Identity)

Мастера (User), auth guards. Client — отдельная модель в ClientPortal.

## Сущности

- `User` — мастер, Sanctum POS token
- Guards: `web` (Filament), `client` (API), `sanctum` (POS)

## Код

- `app/Models/User.php`
- `config/auth.php`
- Application: `app/Application/Identity/` _(будущее)_

## Правила по слоям

| Файл | Слой | Globs |
|------|------|-------|
| `domain.mdc` | Domain | User, policies |
| `application.mdc` | Application | auth use cases |
| `presentation.mdc` | Presentation | login, token endpoints |

## ES

- [05-агрегаты — Идентичность](../../../es/05-агрегаты/README.md#bc-идентичность)
