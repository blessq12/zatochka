# BC: Исполнение заказа (OrderFulfillment)

**Корень домена:** `Order` (ES). Центральный агрегат.

## Состояние кода

| Слой | Статус |
|------|--------|
| Domain | ✅ |
| Infrastructure | ✅ (+ DomPDF) |
| Application | ✅ |
| Presentation | ✅ Filament `/cp`, POS `/api/pos/*`, PDF |

## Domain (`app/Domain/OrderFulfillment/`)

| Папка | Содержание |
|-------|------------|
| `Entity/` | `Order`, `OrderWork`, `OrderTool`, `OrderMaterial` |
| `Enum/` | `OrderStatus`, `OrderSource`, `OrderUrgency`, `PosOrderListTab`, `DocumentType` |
| `ValueObject/` | `OrderNumber`, `ClientSnapshot` |
| `Repository/` | `OrderRepositoryInterface` |
| `Service/` | `OrderNumberGenerator` |
| `Event/` | `OrderCreated`, `OrderTakenToWork`, `WorkAdded`, `OrderReady`, `OrderIssued`, `OrderCancelled`, `OrderPriceRecalculated`, `InternalNotesUpdated`, `EquipmentLinkedToOrder`, `DocumentGenerated` |
| `Exception/` | `OrderPolicyViolation`, `OrderNotFoundException` |

### Поведение `Order`

- `create()` — фабрика, INV-07 (snapshot для гостя)
- Lifecycle: `assignMaster`, `takeToWork`, `markWaitingForParts`, `resume`, `markReady`, `returnToWork`, `issue`, `cancel`
- Содержимое: `addWork`, `removeWork`, `setWorkPrice`, `addMaterial`, `removeMaterial`, `recalculatePrice`, `linkWarranty`, `linkEquipment`, `updateInternalNotes`
- Queries: `isActive()`, `clientDisplayName()`, `clientDisplayPhone()`

### `OrderRepositoryInterface`

`findById`, `save`, `findLastOrderNumberForYear`, `findForMaster`, `countByTabForMaster`, `averageWorkDurationSecondsForMaster`, `findActiveForClient`, `findHistoryForClient`, `findByIdForClient`, `linkGuestOrdersByPhone`, `findByEquipmentId`

## Infrastructure (`app/Infrastructure/OrderFulfillment/`)

| Папка | Классы |
|-------|--------|
| `Persistence/Eloquent/` | `OrderModel`, `OrderWorkModel`, `OrderToolModel`, `OrderMaterialModel` |
| `Persistence/Mapper/` | `OrderMapper` |
| `Persistence/Repository/` | `EloquentOrderRepository` |
| `Pdf/` | `DomPdfRenderer` → `PdfRendererInterface` |

## Application (`app/Application/OrderFulfillment/`)

### Commands

| Команда | Событие |
|---------|---------|
| `CreateOrder` | `OrderCreated` |
| `AssignMasterToOrder` | — |
| `TakeOrderToWork` | `OrderTakenToWork` |
| `MarkOrderWaitingForParts` | — |
| `ResumeOrder` | — |
| `MarkOrderReady` | `OrderReady` |
| `ReturnOrderToWork` | — |
| `IssueOrder` | `OrderIssued` |
| `CancelOrder` | `OrderCancelled` |
| `AddWork` | `WorkAdded` |
| `RemoveWork` | — |
| `SetWorkPrices` | — |
| `RecalculateOrderPrice` | `OrderPriceRecalculated` |
| `AddMaterialToOrder` | — (cross-BC Warehouse) |
| `RemoveMaterialFromOrder` | — |
| `UpdateInternalNotes` | `InternalNotesUpdated` |
| `LinkEquipmentToOrder` | `EquipmentLinkedToOrder` |
| `GenerateDocument` | `DocumentGenerated` |

### Queries

`GetPosOrders`, `GetPosOrderDetail`, `GetPosOrderCounts`, `GetPosDashboard`

### Read models / Presenters

- `PosOrderPresenter` — список и базовая карточка
- `PosOrderReadModelBuilder` — карточка + equipment, master, материалы с именами
- `OrderDocumentReadModelBuilder` — проекция для PDF
- `OrderLoader` — загрузка агрегата

### Port

`PdfRendererInterface` — рендер Blade → PDF bytes

## Presentation

| Канал | Путь |
|-------|------|
| Filament | `Orders/OrderResource` — список, создание, просмотр, действия менеджера |
| PDF | `GET /cp/orders/{id}/documents/{receipt\|handover_act}` |
| POS | `/api/pos/orders/*`, `/api/pos/dashboard` |

## Тесты

- `tests/Unit/Domain/OrderFulfillment/OrderTest.php`
- `tests/Feature/OrderFulfillment/OrderLifecycleTest.php`
- `tests/Feature/OrderFulfillment/OrderPricingTest.php`
- `tests/Feature/OrderFulfillment/DocumentGenerationTest.php`

## ES

- [Order](../../../es/05-агрегаты/README.md#агрегат-order-корень-bc-исполнение-заказа)
- [Команды](../../../es/04-команды/README.md)
- [Read models](../../../es/07-read-models/README.md)
