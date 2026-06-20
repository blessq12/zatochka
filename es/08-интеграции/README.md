# 08 — Интеграции

## Опросник (группа 9) — ✅ завершён (MVP)

| Система | MVP | Направление | Примечание |
|---------|-----|-------------|------------|
| **PDF** (DomPDF / Snappy) | ✅ | исходящая | документы on-the-fly в Filament |
| Telegram | ❌ | — | интерфейс не в MVP |
| Email / SMS | ❌ | — | |
| 1С / бухгалтерия | ❌ вне scope | — | |
| Онлайн-оплата | ❌ вне scope | — | |
| S3 / object storage | ❌ | — | PDF без хранения в MVP |

## Хранение данных bootstrap

Контакты, график, доставка, company, FAQ — **в БД** (`site_settings`, JSON по ключам). Сидер: `DomainSeeder`. Filament CRUD настроек — **не** в MVP.

Прайс — `PriceBlock` / `PriceItem` в BC Справочники.

Фронт: `GET /api/bootstrap` → Pinia `bootstrapStore`; каталог `resources/js/mocks/` удалён.

## Будущее (не MVP)

- Telegram: уведомления клиенту / менеджеру о заявке
- Email: дублирование заявки с сайта
