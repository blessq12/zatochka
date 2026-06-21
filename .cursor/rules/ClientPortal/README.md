# BC: Клиентский портал (ClientPortal)

ЛК, отзывы, заявки с сайта (ES). Владелец `SiteLead`.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ (+ Auth) |
| Application | ✅ |
| Presentation API | ✅ `/api/*`, Filament |
| Presentation Vue | ✅ `/client/dashboard`, формы заявок на публичных страницах |
| Тесты | ⚠️ 1 feature-файл (handler-level) |

## Семантика (ES ↔ код)

| ES / глоссарий | Код | Примечание |
|----------------|-----|------------|
| **Lead** (заявка) | `SiteLead` | entity, table `site_leads`, `SubmitSiteLead` |
| `lead_id` на Order | `Order.leadId` / `lead_id` | `OrderSource::SiteLead` |
| `converted` + `order_id` | `SiteLead::markConverted()` | POL-09, из `CreateOrderHandler` |
| **Client** | `Client` | guard `client`, таблица `clients` |
| Гостевой заказ | `client_snapshot` на Order | `LinkGuestOrdersToClient` по телефону |
| **Review** | `Review` | только после `issued`; модерация в Filament |
| `SetPassword` (ES) | `SetClientPassword` | команда Application |
| `GetClientActiveOrders` / `GetClientOrderHistory` (ES) | один `GetClientOrdersQuery` + флаг `history` | ADR-005: без статусов цеха |
| `GetOrderReview` (ES) | **нет отдельного query** | `review_exists` в presenter + `ReviewRepository` |

## Domain (`app/Domain/ClientPortal/`)

| Папка | Содержание |
|-------|------------|
| `Entity/` | `Client`, `SiteLead`, `Review` |
| `Enum/` | `ReviewStatus` — pending, approved, rejected |
| `Repository/` | `ClientRepositoryInterface`, `SiteLeadRepositoryInterface`, `ReviewRepositoryInterface` |
| `Event/` | `SiteLeadReceived`, `ClientRegistered`, `GuestOrdersLinkedToClient`, `ReviewSubmitted`, `ReviewApproved`, `ReviewRejected` |
| `Exception/` | `SiteLeadPolicyViolation`, `ClientAlreadyRegisteredException`, `ClientNotFoundException`, `ReviewPolicyViolation` |

### Поведение

- `Client::register()`, `updateProfile()`, `markPasswordSet()`, `assignId()`
- `SiteLead::create()`, `markConverted(orderId)` — POL-09
- `Review::submit()`, `approve()`, `reject()`, `assignId()` — отзыв только после `issued` (проверка в Application)

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

`ClientProfilePresenter`, `ClientOrderPresenter` (без статусов цеха — ADR-005, поле `review_exists`), `ReviewPresenter`

### Support

`ClientLoader`

### Cross-BC

`SubmitReviewHandler`, `LinkGuestOrdersToClientHandler`, `GetClientOrdersQueryHandler`, `GetClientOrderDetailQueryHandler` → `OrderRepositoryInterface`  
`CreateOrderHandler` (OrderFulfillment) → `SiteLeadRepositoryInterface`

## Presentation

### API

| Endpoint | Назначение |
|----------|------------|
| `POST /api/leads` | заявка с сайта |
| `POST /api/auth/register`, `login` | регистрация/вход ЛК |
| `GET\|PATCH /api/client/profile` | профиль |
| `POST /api/client/password` | смена пароля |
| `GET /api/client/orders/active\|history\|{id}` | заказы клиента |
| `POST /api/client/orders/{id}/review` | отзыв |

### Filament `/cp` — кластер «Клиенты»

- `Clients/ClientResource` — список клиентов, action «Привязать гостевые заказы»
- `Reviews/ReviewResource` — модерация approve/reject (кнопки только для `pending`)

Лиды — `SiteLeads/SiteLeadResource` в кластере **Заказы** (см. [OrderFulfillment/README](../OrderFulfillment/README.md)).

### Vue SPA

| Маршрут | Назначение |
|---------|------------|
| `/client/dashboard` | ЛК: login/register → профиль, активные, история |
| `/sharpening`, `/repair`, `/delivery` | публичные формы → `POST /api/leads` |

Stores: `authStore` (`auth_token`), `orderStore` (заказы, отзывы). Публичный контент сайта — `bootstrapStore` (фасад [PublicSite](../PublicSite/), см. [Company](../Company/) + [Pricing](../Pricing/)).  
`GET /api/client/orders/{id}` в API есть, во фронте **не вызывается** (списки + модалки на list data).

Подробнее: [presentation.mdc](./presentation.mdc)

## Тесты

`tests/Feature/ClientPortal/ClientPortalTest.php` — заявка, регистрация+link guest, отзыв до/после issued.

## ES

- [Client, Review, Lead](../../../es/05-агрегаты/README.md)
- [Глоссарий: Lead = SiteLead](../../../es/11-глоссарий/README.md)
