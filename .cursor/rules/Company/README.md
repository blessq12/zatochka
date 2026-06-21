# BC: Компания (Company)

Филиал и контент публичного сайта (контакты, график, FAQ и т.д.).

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ✅ |
| Presentation | ✅ Filament «Компания» |

## Domain (`app/Domain/Company/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `Branch`, `SiteContent` |
| `Repository/` | `BranchRepositoryInterface`, `SiteContentRepositoryInterface` |
| `Exception/` | `BranchNotFoundException` |

### Repository ports

| Repository | Метод |
|------------|-------|
| `BranchRepositoryInterface` | `findById`, `findFirstActive`, `save` |
| `SiteContentRepositoryInterface` | `findByKey`, `getValuesByKeys` |

`SiteContent` — json `value` в таблице `site_settings` (ключ: contacts, schedule, company, delivery_info, faq).

## Infrastructure (`app/Infrastructure/Company/`)

Eloquent: `BranchModel`, `SiteContentModel`. Mapper + `EloquentBranchRepository`, `EloquentSiteContentRepository`.

## Application (`app/Application/Company/`)

| Тип | Классы |
|-----|--------|
| Commands | `SaveBranch` |
| Queries | `GetPublicSiteContent` |

## Presentation

### Filament `/cp` (кластер «Компания»)

| Ресурс | Назначение |
|--------|------------|
| `SiteContent/SiteContentResource` | редактирование contacts, schedule, company, delivery_info, faq |
| `Branches/BranchResource` | CRUD филиала |

### Косвенное использование

- `CreateOrderHandler` → `BranchRepositoryInterface::findFirstActive`
- `OrderDocumentReadModelBuilder` → branch + contacts для PDF

## Данные

`CompanySeeder` → `DomainSeeder`: филиал «Центральный», ключи `site_settings`.

## ES

- [Company, Branch, SiteContent](../../../es/05-агрегаты/README.md)
