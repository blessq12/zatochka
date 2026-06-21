## ADR-001: Bounded contexts в монолите

**Статус:** принято  
**Дата:** 2026-06-16  
**Контекст:** проектирование бэкенда после event storming; один деплой Laravel.  
**Решение:** 7 Domain BC (Исполнение заказа, Клиентский портал, Компания, Прайс, Оборудование, Склад, Идентичность) + Application-фасад PublicSite. Общая БД; связь по ID; код в `app/Domain/{BcName}/`.  
**Последствия:** нужны application services для кросс-BC сценариев (CreateOrder из Lead, SubmitReview).  
**Альтернативы:** плоские Models; микросервисы — отклонено для MVP.

> **Эволюция (2026):** BC «Справочники» (Catalog) разделён на Company + Pricing. См. ADR-007.

## ADR-002: Гостевой заказ без Client

**Статус:** принято  
**Дата:** 2026-06-16  
**Контекст:** клиент не обязан регистрироваться.  
**Решение:** `Order.client_id` nullable + `client_snapshot` (имя, телефон). Привязка — `LinkGuestOrdersToClient` менеджером.  
**Последствия:** ЛК и отчёты должны учитывать snapshot.  
**Альтернативы:** find-or-create Client по телефону — отклонено.

## ADR-003: Документы без агрегата

**Статус:** принято  
**Дата:** 2026-06-16  
**Контекст:** акты и квитанции в MVP.  
**Решение:** генерация PDF on-the-fly в Filament; событие `DocumentGenerated` опционально для аудита, хранение файлов — не обязательно в MVP.  
## ADR-004: Публичный bootstrap

**Статус:** принято  
**Дата:** 2026-06-16  
**Контекст:** загрузка публичного SPA.  
**Решение:** один endpoint `GetPublicBootstrap`: прайсы, contacts, schedule, delivery_info, company, faq.  
**Последствия:** один round-trip; контент в `site_settings` (Company) + прайс в Pricing; Vue читает через `bootstrapStore`, без моков. Handler — `PublicSite/GetPublicBootstrap`.  
**Альтернативы:** отдельные `/api/prices/*` — можно оставить как thin wrapper.

## ADR-005: ЛК без статусов цеха

**Статус:** принято  
**Дата:** 2026-06-16  
**Контекст:** прозрачность для клиента vs простота UI.  
**Решение:** в ЛК только bucket active/history; поля: номер, тип, цена, дата, описание.  
**Последствия:** фронт ЛК убрать статус-бейджи; иначе расхождение с доменом.

## ADR-006: Назначение мастера менеджером

**Статус:** принято  
**Дата:** 2026-06-16  
**Контекст:** видимость заказов в POS.  
**Решение:** менеджер вызывает `AssignMasterToOrder` на `new`; мастер видит только заказы с `master_id` = self.  
**Последствия:** новая команда в Filament; POS фильтрует все списки по master_id.  
**Альтернативы:** общая очередь «новых» для всех мастеров — отклонено.

## ADR-007: Split Catalog → Company + Pricing

**Статус:** принято  
**Дата:** 2026-06  
**Контекст:** рост Filament CRUD для контента сайта и прайса; монолитный BC Catalog смешивал ответственности.  
**Решение:** Domain/Infrastructure/Application разделены на **Company** (Branch, SiteContent) и **Pricing** (PriceBlock, PriceItem). Bootstrap — Application-фасад **PublicSite** (`GetPublicBootstrap`).  
**Последствия:** DI через `Company/*` и `Pricing/*` repositories; legacy `Domain/Catalog/` не используется.  
**Альтернативы:** оставить Catalog — отклонено.
