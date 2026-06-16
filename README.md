# ЗАТОЧКА.ТСК

Бэкенд мастерской (заточка + ремонт, Томск). Laravel-монолит, DDD + Hexagonal, 6 bounded contexts.

## Документация домена

Источник истины по бизнес-логике: [`es/`](es/README.md) (Event Storming, 12 групп — завершён).

Корневой контекст: **OrderFulfillment** → агрегат **Order**.

## Статус слоёв

| Слой | Статус |
|------|--------|
| `app/Domain/{BC}/` | ✅ сущности, enum, ports, поведение Order |
| `app/Infrastructure/{BC}/` | ✅ Eloquent, Mapper, Repository |
| `app/Application/{BC}/` | ✅ все 6 BC (use cases) |
| API публичный `/api/*` | ✅ bootstrap, leads, auth, ЛК |
| API POS `/api/pos/*` | ✅ заказы, склад, оборудование, dashboard |
| Filament `/cp` | ✅ заказы, заявки, клиенты, отзывы, склад, оборудование |

Подробнее: [`.cursor/index.md`](.cursor/index.md), [`app/Application/README.md`](app/Application/README.md).

## Структура

```
app/
├── Domain/{BC}/          # Entity, Enum, Repository (ports), Service
├── Application/{BC}/     # Command, Query, Handler, Presenter
├── Infrastructure/{BC}/  # Persistence, Auth
└── Shared/ValueObject/   # EntityId, Phone, Email, Money
```

BC: `OrderFulfillment`, `ClientPortal`, `Catalog`, `Equipment`, `Warehouse`, `Identity`.

## Запуск

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## План реализации

1. **Фаза 0** — Domain: поведение Order, инварианты ✅
2. **Фаза 1** — lifecycle заказа (Application + Filament + POS) ✅
3. **Фаза 2** — цены, материалы, заметки мастера ✅
4. **Фаза 3** — клиентский контур (Lead, ЛК, отзывы) ✅
5. **Фаза 4** — bootstrap, склад, оборудование ✅
6. **Фаза 5** — PDF on-the-fly, read models ✅
