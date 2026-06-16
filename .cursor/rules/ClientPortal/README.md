# BC: Клиентский портал (ClientPortal)

Регистрация, ЛК, отзывы, приём заявок с сайта.

## Агрегаты

- `Client` — auth публичного API
- `Review` — отзывы после выдачи заказа
- `SiteLead` — заявка с сайта (владелец записи)

## Код

- Domain: `app/Domain/ClientPortal/`
- Application: `app/Application/ClientPortal/` _(будущее)_

## Правила по слоям

| Файл | Слой | Globs |
|------|------|-------|
| `domain.mdc` | Domain | `app/Domain/ClientPortal/**` |
| `application.mdc` | Application | `app/Application/ClientPortal/**` |
| `presentation.mdc` | Presentation | публичный `/api/*` |

## ES

- [05-агрегаты — Client, Review, Lead](../../../es/05-агрегаты/README.md)
