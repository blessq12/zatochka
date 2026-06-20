# BC: Идентичность (Identity)

Мастера (User/Master). Клиент — отдельный BC ClientPortal.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ⬜ каркас (login в Presentation) |
| Presentation | ✅ POS login, Filament web auth |

## Domain (`app/Domain/Identity/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `Master` — name, surname, email, phone, notificationsEnabled |
| `Repository/` | `MasterRepositoryInterface` — `findById`, `findByEmail`, `save` |

## Infrastructure (`app/Infrastructure/Identity/`)

| Папка | Классы |
|-------|--------|
| `Persistence/Eloquent/` | `UserModel` — таблица `users`, Sanctum, Factory |
| `Persistence/Mapper/` | `MasterMapper` |
| `Persistence/Repository/` | `EloquentMasterRepository` |

## Laravel glue

- `app/Models/User.php` extends `UserModel`
- `config/auth.php`: guard `web` → User; guard `client` → `ClientAuthModel` (ClientPortal)
- `DomainSeeder`: `master@zatochka.local`, `manager@zatochka.local` / `password`

## Presentation (без Application handlers)

| Канал | Реализация |
|-------|------------|
| POS | `PosController::login` — Sanctum token для `UserModel`; см. `.cursor/rules/POS/` |
| Filament | guard `web`, встроенный login `/cp/login` |
| Read models | `MasterRepository` в `PosOrderReadModelBuilder`, `OrderDocumentReadModelBuilder` |

## Нет в коде

- `Application/Identity` commands (RegisterMaster, …)
- Filament CRUD мастеров
- Policies по ролям

## ES

- [Идентичность](../../../es/05-агрегаты/README.md#bc-идентичность)
