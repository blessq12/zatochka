# BC: Клиентский портал (ClientPortal)

ЛК, отзывы, заявки с сайта (ES). Владелец `SiteLead`.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ (+ Auth) |
| Application | ⬜ каркас |
| Presentation | ⬜ нет `/api` по BC |

## Domain (`app/Domain/ClientPortal/`)

| Папка | Классы |
|-------|--------|
| `Entity/` | `Client`, `SiteLead`, `Review` |
| `Enum/` | `ReviewStatus` |
| `Repository/` | `ClientRepositoryInterface`, `SiteLeadRepositoryInterface`, `ReviewRepositoryInterface` |

## Infrastructure (`app/Infrastructure/ClientPortal/`)

| Папка | Классы |
|-------|--------|
| `Persistence/Eloquent/` | `ClientModel`, `SiteLeadModel`, `ReviewModel` |
| `Persistence/Mapper/` | `ClientMapper`, `SiteLeadMapper`, `ReviewMapper` |
| `Persistence/Repository/` | `EloquentClientRepository`, `EloquentSiteLeadRepository`, `EloquentReviewRepository` |
| `Auth/` | `ClientAuthModel` — guard `client` в `config/auth.php` |

Domain `Client` и `ClientAuthModel` — разные классы (entity vs Laravel auth).

## Application / Presentation

Не реализованы. ES: `SubmitSiteLead`, `RegisterClient`, `SubmitReview`, публичный API.

## ES

- [Client, Review, Lead](../../../es/05-агрегаты/README.md)
