# BC: Справочники (Catalog)

Прайс, филиал, настройки сайта. Read-heavy.

## Агрегаты / сущности

- `PriceBlock`, `PriceItem` — прайс заточка/ремонт
- `Branch` — одна запись (техническое поле на Order)
- `SiteSetting` — контакты, bootstrap

## Код

- Domain: `app/Domain/Catalog/`
- Application: `app/Application/Catalog/` _(будущее)_

## Правила по слоям

| Файл | Слой | Globs |
|------|------|-------|
| `domain.mdc` | Domain | `app/Domain/Catalog/**` |
| `application.mdc` | Application | `app/Application/Catalog/**` |
| `presentation.mdc` | Presentation | Filament + GetPublicBootstrap |

## ES

- [05-агрегаты — Справочники](../../../es/05-агрегаты/README.md#справочники-bc-справочники)
