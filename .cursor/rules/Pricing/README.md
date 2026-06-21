# BC: Прайс (Pricing)

Блоки и позиции прайс-листа (заточка / ремонт).

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ |
| Application | ✅ |
| Presentation | ✅ Filament «Прайс-лист» |

## Domain (`app/Domain/Pricing/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `PriceBlock`, `PriceItem` |
| `Enum/` | `PriceType` (sharpening, repair), `PricePrefix` (from, to) |
| `Repository/` | `PriceBlockRepositoryInterface`, `PriceItemRepositoryInterface` |

### Repository ports

| Repository | Метод |
|------------|-------|
| `PriceBlockRepositoryInterface` | `findById`, `save`, `findAllOrdered` |
| `PriceItemRepositoryInterface` | `findById`, `save`, `findByPriceBlockId` |

## Infrastructure (`app/Infrastructure/Pricing/`)

Eloquent: `PriceBlockModel`, `PriceItemModel`. Mapper + Repository.

## Application (`app/Application/Pricing/`)

| Тип | Классы |
|-----|--------|
| Commands | `SavePriceItem` |
| Queries | `GetPublicPriceList` |

Ответ `GetPublicPriceList`: blocks с `type`, `title`, `items[]` (name, price, prefix, description).

## Presentation

### Filament `/cp` (кластер «Прайс-лист»)

| Ресурс | Тип |
|--------|-----|
| `SharpeningPriceItems/SharpeningPriceItemResource` | PriceType::Sharpening |
| `RepairPriceItems/RepairPriceItemResource` | PriceType::Repair |

Базовый класс: `AbstractPriceItemResource` → `SavePriceItemHandler`.

### Vue SPA

Прайс на публичных страницах — через `GET /api/bootstrap` → `data.prices` (см. [PublicSite](../PublicSite/)).

## Данные

`PricingSeeder` → `DomainSeeder`: блоки «Заточка инструмента», «Ремонт».

## Тесты

`tests/Feature/PublicSite/BootstrapTest.php` (prices в bootstrap).

## ES

- [Pricing](../../../es/05-агрегаты/README.md)
