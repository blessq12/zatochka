# BC: Справочники (Catalog)

Прайс, филиал, настройки сайта. Read-heavy (ES).

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ✅ `GetPublicBootstrap` |
| Presentation | ✅ `GET /api/bootstrap`, Vue `bootstrapStore` |

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
| `GetPublicBootstrap` | `GetPublicBootstrapQueryHandler` | prices, contacts, schedule, delivery_info, company, faq |

### Контракт `GET /api/bootstrap` → `data`

| Ключ | Содержание (JSON в `site_settings`) |
|------|-------------------------------------|
| `prices` | блоки прайса из `PriceBlock` + `PriceItem` |
| `contacts` | contact_person, phone, phone_tel, email, address{main,details[]}, social{email, links[]} |
| `schedule` | days[] — name, is_day_off, workshop, delivery, day_off_text |
| `delivery_info` | free_conditions[], advantages[]{title, description} |
| `company` | name, owner_name, inn, ogrn, legal_address, actual_address |
| `faq` | items[]{id, question, answer_lines[]} |

## Presentation

### API

- `GET /api/bootstrap` — `BootstrapController`
- Данные для PDF — `OrderDocumentReadModelBuilder` (contacts.address → string для PDF)
- Filament CRUD прайса/настроек — **не** реализован (данные через seeder)

### Vue SPA (публичный сайт, не `/pos`)

| Store | Файл | Роль |
|-------|------|------|
| `bootstrapStore` | `resources/js/stores/bootstrapStore.js` | один запрос при старте (`App.vue`), кэш в Pinia |

**Потребители:** Home (FAQ, график), Contacts, WorkSchedule, Delivery, Footer, Price/Sharpening/Repair (прайс).

**Моки API:** удалены (`resources/js/mocks/`). Axios только на бэкенд.

## Данные

`DomainSeeder`: филиал «Центральный», 2 блока прайса, `site_settings` — contacts, schedule, delivery_info, company, faq.

## Тесты

`tests/Feature/Catalog/BootstrapTest.php`

## ES

- [Справочники](../../../es/05-агрегаты/README.md#справочники-bc-справочники)
- [GetPublicBootstrap](../../../es/07-read-models/README.md)
