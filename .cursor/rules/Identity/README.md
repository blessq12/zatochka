# BC: Идентичность (Identity)

Мастера и менеджеры (`User` / `Master`). Клиент — отдельный BC ClientPortal.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ✅ Register/Update master |
| Presentation | ✅ POS login, Filament CRUD, web auth |

## Domain (`app/Domain/Identity/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `Master` — name, surname, email, phone |
| `Enum/` | `UserRole` — master, manager (Filament scope) |
| `Repository/` | `MasterRepositoryInterface` — `findById`, `findByEmail`, `save` |
| `Exception/` | `MasterNotFoundException`, `MasterAlreadyExistsException` |

## Infrastructure (`app/Infrastructure/Identity/`)

| Папка | Классы |
|-------|--------|
| `Persistence/Eloquent/` | `UserModel` — таблица `users`, Sanctum, role |
| `Persistence/Mapper/` | `MasterMapper` |
| `Persistence/Repository/` | `EloquentMasterRepository` |

## Application (`app/Application/Identity/`)

| Commands | Handlers |
|----------|----------|
| `RegisterMaster` | `RegisterMasterHandler` |
| `UpdateMaster` | `UpdateMasterHandler` |

POS login — без Application handler (`PosController::login`).

## Laravel glue

- `app/Models/User.php` extends `UserModel`
- `config/auth.php`: guard `web` → User; guard `client` → `ClientAuthModel` (ClientPortal)
- `IdentitySeeder`: `root@root.com` (manager), `master@master.com`, `ivan.petrov@zatochka.local`, … / `password`

## Presentation

| Канал | Реализация |
|-------|------------|
| POS | `PosController::login` — Sanctum token; см. [POS](../POS/) |
| Filament | guard `web`, `/cp/login`; кластер «Идентичность»: `Masters/MasterResource`, `Managers/ManagerResource` |
| Read models | `MasterRepository` в `PosOrderReadModelBuilder`, `OrderDocumentReadModelBuilder` |

## Нет в коде

- Policies по ролям (кроме scope в Filament resources)
- Application handler для POS login

## ES

- [Идентичность](../../../es/05-агрегаты/README.md#bc-идентичность)
