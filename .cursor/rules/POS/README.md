# Вертикаль: POS мастера

**Не отдельный BC.** Presentation-вертикаль поверх OrderFulfillment, Equipment, Warehouse, Identity.

Мастер работает с заказами, складом (read-only) и реестром оборудования через SPA `/pos` и API `/api/pos/*`.

## Состояние кода

| Слой | Статус |
|------|--------|
| API | ✅ `PosController`, Sanctum |
| Application | ✅ use cases в BC-источниках (см. ниже) |
| Vue SPA | ✅ `/pos/*`, Pinia, services |
| Тесты | ⚠️ частично (`DocumentGenerationTest`, `EquipmentTest`) |

## Семантика (ES ↔ код)

| ES / бизнес | Код | Примечание |
|-------------|-----|------------|
| Мастер | `UserModel` / `Master` (Identity) | login → Sanctum token |
| Воронка заказа | `OrderStatus`, `PosOrderListTab` | табы POS ≠ сырые статусы |
| Новые | `PosOrderListTab::New` → `status=new` | только заказы с `master_id = auth` |
| В работе | `PosOrderListTab::Active` | `in_work` |
| Ожидание запчастей | `PosOrderListTab::WaitingParts` | `waiting_parts` |
| Готовые | `PosOrderListTab::Completed` | `status=ready` (ещё не `issued`) |
| Взять в работу | `TakeOrderToWork` | мастер уже назначен в Filament |
| Выдача / отмена | `IssueOrder`, `CancelOrder` | **только Filament** |
| Назначение мастера | `AssignMasterToOrder` | **только Filament** |
| Цены, материалы, PDF | Filament `OrderManageActions` | **не в POS API** |

### Табы POS vs статусы Order

| Таб POS (`status` query) | `OrderStatus` в БД | Счётчик API (`counts`) |
|--------------------------|-------------------|------------------------|
| `new` | `new` | `new` |
| `active` | `in_work` | `active` |
| `waiting_parts` | `waiting_parts` | `waiting_parts` |
| `completed` | `ready` | `completed` |

Фронт в `OrderService.getOrdersCount()` маппит `active` → `in_work`, `completed` → `ready` для UI.

## BC и use cases

### OrderFulfillment (основной)

**Commands (POS):** `TakeOrderToWork`, `MarkOrderWaitingForParts`, `ResumeOrder`, `AddWork`, `RemoveWork`, `UpdateInternalNotes`, `MarkOrderReady`, `ReturnOrderToWork`

**Queries:** `GetPosDashboard`, `GetPosOrderCounts`, `GetPosOrders`, `GetPosOrderDetail`

**Presenter / ReadModel:** `PosOrderPresenter` (списки), `PosOrderReadModelBuilder` (карточка: equipment, master, имена материалов)

### Equipment

**Queries:** `SearchEquipment`, `GetEquipmentOrderHistory` (cross-BC → `OrderRepositoryInterface`)

**Presenter:** `EquipmentPresenter`

История по `equipment_id` — **без** фильтра по текущему мастеру.

### Warehouse

**Query:** `SearchWarehouseItems` — read-only для мастера

**Presenter:** `WarehouseItemPresenter`

### Identity

Login — `PosController::login` (без Application handler): `UserModel` + Sanctum token `pos`.

Read-side: `MasterRepository` в `PosOrderReadModelBuilder`.

## Presentation

| Канал | Путь |
|-------|------|
| API | `/api/pos/*` — см. [presentation.mdc](./presentation.mdc) |
| Vue SPA | `/pos/*` — см. [presentation.mdc](./presentation.mdc) |
| Filament | `/cp` — менеджерские действия, не дублируются в POS |

## Зависимость POS от Filament

POS не создаёт заказы и не назначает мастера. Типичный поток:

1. Менеджер: заявка → `CreateOrder` + `AssignMasterToOrder` в Filament
2. Мастер: «Новые» в POS → `take-to-work` → работа в цехе
3. Менеджер: цены, материалы, `IssueOrder`, PDF — в Filament

## Тесты

- `tests/Feature/OrderFulfillment/DocumentGenerationTest.php` — dashboard, карточка заказа
- `tests/Feature/Equipment/EquipmentTest.php` — поиск оборудования

## ES

- [Акторы: мастер](../../../es/01-акторы/README.md)
- [Order, lifecycle](../../../es/05-агрегаты/README.md#агрегат-order-корень-bc-исполнение-заказа)
- [Read models POS](../../../es/07-read-models/README.md)
