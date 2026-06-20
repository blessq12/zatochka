# BC: Справочники (Catalog)

Прайс, филиал, настройки сайта. Read-heavy (ES).

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ✅ `GetPublicBootstrap` |
| Presentation | ✅ `GET /api/bootstrap` |

## Domain (`app/Domain/Catalog/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `Branch`, `PriceBlock`, `PriceItem`, `SiteSetting` |
| `Enum/` | `PriceType` — sharpening, repair |
| `Repository/` | 4 interface |

### Методы репозиториев (расширения для read)

| Repository | Метод |
|------------|-------|
| `BranchRepositoryInterface` | `findById`, `findFirstActive`, `save` |
| `PriceBlockRepositoryInterface` | `findById`, `save`, `findAllOrdered` |
| `PriceItemRepositoryInterface` | `findById`, `save`, `findByPriceBlockId` |
| `SiteSettingRepositoryInterface` | `findByKey`, `save`, `getValuesByKeys` |

## Infrastructure (`app/Infrastructure/Catalog/`)

Eloquent×4, Mapper×4, `Eloquent*Repository` ×4.

## Application (`app/Application/Catalog/`)

| Query | Handler | Ответ |
|-------|---------|-------|
| `GetPublicBootstrap` | `GetPublicBootstrapQueryHandler` | prices, contacts, schedule, delivery_info, company |

## Presentation

- `GET /api/bootstrap` — `BootstrapController`
- Данные для PDF — через `SiteSettingRepository` + `BranchRepository` в `OrderDocumentReadModelBuilder`
- Filament CRUD прайса — **не** реализован (данные через seeder)

## Данные

`DomainSeeder`: филиал «Центральный», 2 блока прайса, `site_settings` (contacts, schedule, delivery_info, company).

## Тесты

`tests/Feature/Catalog/BootstrapTest.php`

## ES

- [Справочники](../../../es/05-агрегаты/README.md#справочники-bc-справочники)
- [GetPublicBootstrap](../../../es/07-read-models/README.md)
