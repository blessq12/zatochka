# BC: Клиентский портал (ClientPortal)

ЛК, отзывы, заявки с сайта (ES). Владелец `SiteLead`.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ (+ Auth) |
| Application | ✅ |
| Presentation | ✅ `/api/*`, Filament |

## Domain (`app/Domain/ClientPortal/`)

| Папка | Содержание |
|-------|------------|
| `Entity/` | `Client`, `SiteLead`, `Review` |
| `Enum/` | `ReviewStatus` |
| `Repository/` | `ClientRepositoryInterface`, `SiteLeadRepositoryInterface`, `ReviewRepositoryInterface` |
| `Event/` | `SiteLeadReceived`, `ClientRegistered`, `GuestOrdersLinkedToClient`, `ReviewSubmitted`, `ReviewApproved`, `ReviewRejected` |
| `Exception/` | `SiteLeadPolicyViolation`, `ClientAlreadyRegisteredException`, `ClientNotFoundException`, `ReviewPolicyViolation` |

### Поведение

- `Client::register()`, `updateProfile()`
- `SiteLead::create()`, `markConverted(orderId)` — POL-09
- `Review::submit()`, `approve()`, `reject()` — отзыв только после `issued`

## Infrastructure (`app/Infrastructure/ClientPortal/`)

| Папка | Классы |
|-------|--------|
| `Persistence/Eloquent/` | `ClientModel`, `SiteLeadModel`, `ReviewModel` |
| `Persistence/Mapper/` | `ClientMapper`, `SiteLeadMapper`, `ReviewMapper` |
| `Persistence/Repository/` | `EloquentClientRepository`, `EloquentSiteLeadRepository`, `EloquentReviewRepository` |
| `Auth/` | `ClientAuthModel` — guard `client` |

## Application (`app/Application/ClientPortal/`)

### Commands

`SubmitSiteLead`, `RegisterClient`, `UpdateClientProfile`, `SetClientPassword`, `SubmitReview`, `LinkGuestOrdersToClient`, `ApproveReview`, `RejectReview`

### Queries

`GetClientProfile`, `GetClientOrders` (active/history), `GetClientOrderDetail`

### Presenters

`ClientProfilePresenter`, `ClientOrderPresenter` (без статусов цеха — ADR), `ReviewPresenter`

### Cross-BC

`SubmitReviewHandler`, `LinkGuestOrdersToClientHandler` → `OrderRepositoryInterface`

## Presentation

| Endpoint | Назначение |
|----------|------------|
| `POST /api/leads` | заявка с сайта |
| `POST /api/auth/register`, `login` | регистрация/вход ЛК |
| `GET\|PATCH /api/client/profile` | профиль |
| `POST /api/client/password` | смена пароля |
| `GET /api/client/orders/active\|history\|{id}` | заказы клиента |
| `POST /api/client/orders/{id}/review` | отзыв |

Filament: `SiteLeads`, `Clients`, `Reviews` resources.

## Тесты

`tests/Feature/ClientPortal/ClientPortalTest.php`

## ES

- [Client, Review, Lead](../../../es/05-агрегаты/README.md)
