# BC: Справочники (Catalog)

Прайс, филиал, настройки сайта. Read-heavy (ES).

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ⬜ каркас |
| Presentation | ⬜ нет bootstrap API / Filament |

## Domain (`app/Domain/Catalog/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `Branch`, `PriceBlock`, `PriceItem`, `SiteSetting` |
| `Enum/` | `PriceType` — sharpening, repair |
| `Repository/` | `BranchRepositoryInterface`, `PriceBlockRepositoryInterface`, `PriceItemRepositoryInterface`, `SiteSettingRepositoryInterface` |

## Infrastructure (`app/Infrastructure/Catalog/`)

| Папка | Классы |
|-------|--------|
| `Persistence/Eloquent/` | `BranchModel`, `PriceBlockModel`, `PriceItemModel`, `SiteSettingModel` |
| `Persistence/Mapper/` | по одному на entity |
| `Persistence/Repository/` | `Eloquent*Repository` ×4 |

## Данные

`DomainSeeder` создаёт: филиал «Центральный», 2 блока прайса, `site_settings` (contacts, schedule, …).

## Application / Presentation

Не реализованы. ES: `GetPublicBootstrap`, Filament CRUD прайса.

## ES

- [Справочники](../../../es/05-агрегаты/README.md#справочники-bc-справочники)
