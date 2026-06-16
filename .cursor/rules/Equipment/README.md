# BC: Оборудование (Equipment)

Реестр оборудования, серийные номера. История ремонтов — query через Order.

## Агрегаты

- `Equipment` — brand, model, serial_numbers (json)

## Код

- Domain: `app/Domain/Equipment/`
- Application: `app/Application/Equipment/` _(будущее)_

## Правила по слоям

| Файл | Слой | Globs |
|------|------|-------|
| `domain.mdc` | Domain | `app/Domain/Equipment/**` |
| `application.mdc` | Application | `app/Application/Equipment/**` |
| `presentation.mdc` | Presentation | Filament + POS read |

## ES

- [05-агрегаты — Equipment](../../../es/05-агрегаты/README.md#агрегат-equipment-bc-оборудование)
