# 05 — Агрегаты и bounded contexts

## Опросник (группа 6) — ✅ завершён

## Архитектурные решения

| Решение | Выбор |
|---------|-------|
| Разделение | Строгие BC, один деплой (монолит) |
| Интеграция BC | Общая БД; сервисы не лезут в чужие модели напрямую |
| Код | `app/Domain/{BcName}/` |
| Документы | PDF on-the-fly в Filament, **не** доменный агрегат |
| Branch | Одна запись в БД, технически на всех заказах |

---

## Карта bounded contexts

```mermaid
flowchart TB
    subgraph CP [BC: Клиентский портал]
        Client
        Review
        LeadIn[Lead]
    end

    subgraph OF [BC: Исполнение заказа]
        Order
        Work
        Tool
        OrderMaterial
    end

    subgraph EQ [BC: Оборудование]
        Equipment
    end

    subgraph WH [BC: Склад]
        WarehouseItem
    end

    subgraph CAT [BC: Справочники]
        PriceBlock
        Branch
    end

    subgraph ID [BC: Идентичность]
        Master[User / Master]
    end

    LeadIn -->|CreateOrder| Order
    Client -.->|client_id optional| Order
    Order -->|equipment_id| Equipment
    OrderMaterial -->|warehouse_item_id| WarehouseItem
    Order -->|master_id| Master
    Order -->|branch_id| Branch
    Review -->|order_id| Order
```

| BC | Ответственность | Агрегаты / сущности |
|----|-----------------|---------------------|
| **Исполнение заказа** | Воронка, работы, цена, содержимое заказа | `Order` (корень), `Lead` |
| **Клиентский портал** | Регистрация, ЛК, отзывы, приём заявок с сайта | `Client`, `Review`, `Lead` (приём) |
| **Оборудование** | Реестр, серийники, история через заказы | `Equipment` |
| **Склад** | Номенклатура, остатки, ручное списание | `WarehouseItem` |
| **Справочники** | Прайс, филиал | `PriceBlock`, `Branch` |
| **Идентичность** | Мастера, auth Sanctum | `User` (не доменный агрегат) |

**Примечание:** `Lead` — на стыке BC (создаётся в клиентском портале, конвертируется в исполнении). Владелец записи — **Клиентский портал**; конвертация — команда из **Исполнения** с ссылкой `lead_id` на `Order`.

---

## Агрегат: Order (корень BC «Исполнение заказа»)

### Сущности внутри агрегата

| Сущность | Описание |
|----------|----------|
| `Work` | Наименование работы; цена — назначает менеджер (`price` nullable до SetWorkPrices) |
| `OrderMaterial` | `warehouse_item_id`, `quantity`, `unit_price`, `total_price` (снимок цены) |
| `Tool` | Позиция заточки: тип инструмента, количество |

### Атрибуты корня

| Поле | Тип | Примечание |
|------|-----|------------|
| `order_number` | string | ORD-…, уникальный, при создании |
| `status` | enum | new, in_work, waiting_parts, ready, issued, cancelled |
| `service_types` | flags/array | sharpening, repair (может быть оба) |
| `urgency` | enum? | standard / urgent |
| `is_warranty` | bool | + `warranty_parent_order_id` |
| `needs_delivery` | bool | |
| `delivery_address` | string? | snapshot или из клиента |
| `problem_description` | text? | ремонт |
| `internal_notes` | text? | мастер |
| `price` | decimal? | после RecalculateOrderPrice |
| `source` | enum | manual, site_lead |
| `lead_id` | FK? | если из заявки |
| `client_id` | FK? | **nullable** |
| `client_snapshot` | json | имя, телефон — для гостя и снимка |
| `equipment_id` | FK? | BC Оборудование |
| `master_id` | FK? | назначается при TakeOrderToWork |
| `branch_id` | FK | одна запись Branch |

### Жизненный цикл

`new` → `in_work` → `waiting_parts` ⇄ `in_work` → `ready` ⇄ `in_work` → `issued` | `cancelled` (только из `new`)

### Инварианты

- INV-01 … INV-05 — см. [06-политики](../06-политики/README.md)
- `Work` и `OrderMaterial` не существуют вне `Order`
- Заточка: `Tool[]`; ремонт: ссылка на `Equipment` + `problem_description`

---

## Агрегат: Lead (BC «Клиентский портал», запись)

| Поле | Примечание |
|------|------------|
| контакты, тип услуги, комментарий, needs_delivery | из SubmitSiteLead |
| `converted` | bool |
| `order_id` | FK после CreateOrder |

**Без жизненного цикла** — только флаг `converted` + ссылка на заказ.

---

## Агрегат: Client (BC «Клиентский портал»)

| Поле | Примечание |
|------|------------|
| `phone` | уникальный, ключ поиска |
| `full_name`, `email`, `delivery_address`, `birth_date` | профиль ЛК |
| `password` | auth |

**Связь с Order:** `client_id` optional; при гостевом заказе — `client_snapshot` в Order. `LinkGuestOrdersToClient` — менеджер.

---

## Агрегат: Review (BC «Клиентский портал»)

| Поле | Примечание |
|------|------------|
| `order_id` | FK → Order (только чтение статуса) |
| `rating`, `comment` | |
| `status` | pending / approved / rejected |

Не отдельный корень домена исполнения — живёт в клиентском BC.

---

## Агрегат: Equipment (BC «Оборудование»)

| Поле | Примечание |
|------|------------|
| `brand`, `model`, `name` | |
| `serial_numbers` | json[] — несколько SN |
| история | query через Order по `equipment_id` |

Order хранит `equipment_id`; данные не дублируются (кроме snapshot при необходимости — **не в MVP**).

---

## Агрегат: WarehouseItem (BC «Склад»)

| Поле | Примечание |
|------|------------|
| `name`, `sku`, `category` | |
| `quantity` | остаток |
| `unit`, `price` | |

**MVP:** справочник + `quantity`; приход/списание — ручные команды менеджера. Автосписание при AddMaterialToOrder — **нет**.

---

## Справочники (BC «Справочники»)

### PriceBlock

- `title`, `items[]` (`name`, `price`, `description?`)
- Тип: sharpening | repair
- Read-heavy; правка в Filament

### Branch

- Одна запись (напр. «Центральный»)
- `branch_id` на Order — техническое поле

---

## BC «Идентичность»

- `User` — мастер, Sanctum `pos` token
- `Client` — отдельная модель/auth для публичного API (не Laravel User)
- Не смешивать guards: `web` (Filament), `client` (API), `sanctum` (POS)

---

## Структура кода (текущая)

Монолит Laravel, hexagonal, BC-first на каждом слое. Подробнее: `.cursor/rules/` и `.cursor/index.md`.

```
app/
├── Shared/ValueObject/              # EntityId, Phone, Email, Money (VO; в домене пока не везде используются)
├── Domain/{BC}/
│   ├── Entity/                      # чистые сущности / агрегаты
│   ├── Enum/ ValueObject/           # где нужно
│   ├── Repository/                  # ports (interfaces)
│   └── Service/                     # OrderNumberGenerator (OrderFulfillment)
├── Application/{BC}/                # каркас Command|Query|Presenter — классов нет
├── Infrastructure/{BC}/
│   ├── Persistence/Eloquent|Mapper|Repository/
│   └── Auth/                        # ClientPortal (ClientAuthModel), Identity (UserModel)
├── Infrastructure/Shared/Provider/PersistenceServiceProvider.php
└── Models/User.php                  # алиас UserModel (Identity)
```

| BC | Domain Entity | Infrastructure |
|----|---------------|----------------|
| OrderFulfillment | Order, OrderWork, OrderTool, OrderMaterial | OrderModel + children, EloquentOrderRepository |
| ClientPortal | Client, SiteLead, Review | *Model, ClientAuthModel |
| Catalog | Branch, PriceBlock, PriceItem, SiteSetting | *Model + repos |
| Equipment | Equipment | EquipmentModel |
| Warehouse | WarehouseItem, StockMovement | *Model |
| Identity | Master | UserModel |

**Реализовано:** Domain + Infrastructure (persistence), миграции, DomainSeeder (Catalog + demo Master); поведение `Order` (переходы статусов, POL/INV), `SiteLead::markConverted`.  
**Не реализовано:** Application handlers, API/Filament/POS, domain events.

**Правило:** cross-BC в Domain — только `*_id`; orchestration — будущий Application layer.

---

## Что не является агрегатом в MVP

| Сущность | Решение |
|----------|---------|
| Document | PDF on-the-fly, без хранения |
| Payment | вне scope |
| Courier / Delivery route | флаг + адрес на Order |
| Notification | не в MVP |
