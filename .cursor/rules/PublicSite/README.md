# Фасад: Публичный сайт (PublicSite)

Application-only слой. Оркестрация bootstrap для SPA — **не** отдельный Domain BC.

## Состояние кода

| Слой | Статус |
|------|--------|
| Application | ✅ `GetPublicBootstrap` |
| Presentation | ✅ `GET /api/bootstrap`, Vue `bootstrapStore` |

## Application (`app/Application/PublicSite/`)

| Query | Handler | Делегирует |
|-------|---------|------------|
| `GetPublicBootstrap` | `GetPublicBootstrapQueryHandler` | `GetPublicSiteContent` (Company) + `GetPublicPriceList` (Pricing) |

### Контракт `GET /api/bootstrap` → `data`

| Ключ | Источник BC |
|------|-------------|
| `prices` | Pricing |
| `contacts` | Company (`site_settings`) |
| `schedule` | Company |
| `delivery_info` | Company |
| `company` | Company |
| `faq` | Company |

## Presentation

### API

- `GET /api/bootstrap` — `BootstrapController` → `GetPublicBootstrapQueryHandler`

### Vue SPA

| Store | Роль |
|-------|------|
| `bootstrapStore` | один запрос при старте (`App.vue`), кэш в Pinia |

**Потребители:** Home, Contacts, WorkSchedule, Delivery, Footer, Price/Sharpening/Repair.

Моки API удалены — axios только на бэкенд.

## Тесты

`tests/Feature/PublicSite/BootstrapTest.php`

## ES

- [GetPublicBootstrap](../../../es/07-read-models/README.md)
