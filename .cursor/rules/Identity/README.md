# BC: Идентичность (Identity)

Мастера (User/Master). Клиент — отдельный BC ClientPortal.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ⬜ каркас |
| Presentation | ⬜ (только Laravel auth config) |

## Domain (`app/Domain/Identity/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `Master` — name, surname, email, phone, notificationsEnabled |
| `Repository/` | `MasterRepositoryInterface` |

## Infrastructure (`app/Infrastructure/Identity/`)

| Папка | Классы |
|-------|--------|
| `Persistence/Eloquent/` | `UserModel` — таблица `users`, Sanctum, Factory |
| `Persistence/Mapper/` | `MasterMapper` |
| `Persistence/Repository/` | `EloquentMasterRepository` |

## Laravel glue

- `app/Models/User.php` extends `UserModel`
- `config/auth.php`: guard `web` → User; guard `client` → `ClientAuthModel` (ClientPortal)
- `DomainSeeder`: demo `master@zatochka.local`

## Application / Presentation

Нет POS login, Filament user CRUD, policies.

## ES

- [Идентичность](../../../es/05-агрегаты/README.md#bc-идентичность)
