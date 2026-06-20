# Application Layer

Use cases по bounded contexts.

## Статус

### OrderFulfillment
Lifecycle, POS queries, цены, материалы, привязка оборудования.

### ClientPortal
| Тип | Реализовано |
|-----|-------------|
| **Commands** | `SubmitSiteLead`, `RegisterClient`, `UpdateClientProfile`, `SetClientPassword`, `SubmitReview`, `LinkGuestOrdersToClient`, `ApproveReview`, `RejectReview` |
| **Queries** | `GetClientProfile`, `GetClientOrders` (active/history), `GetClientOrderDetail` |
| **Presenters** | `ClientProfilePresenter`, `ClientOrderPresenter`, `ReviewPresenter` |
| **Events** | `SiteLeadReceived`, `ClientRegistered`, `GuestOrdersLinkedToClient`, `ReviewSubmitted`, `ReviewApproved`, `ReviewRejected` |

### Catalog
| Тип | Реализовано |
|-----|-------------|
| **Queries** | `GetPublicBootstrap` → prices, contacts, schedule, delivery_info, company, faq |

Потребитель SPA: `resources/js/stores/bootstrapStore.js`.

### Warehouse
| Тип | Реализовано |
|-----|-------------|
| **Commands** | `ReceiveStock`, `WriteOffStock` |
| **Queries** | `SearchWarehouseItems` |
| **Presenters** | `WarehouseItemPresenter` |
| **Events** | `StockReceived`, `StockWrittenOff` |

### Equipment
| Тип | Реализовано |
|-----|-------------|
| **Commands** | `RegisterEquipment` |
| **Queries** | `SearchEquipment`, `GetEquipmentOrderHistory` |
| **Presenters** | `EquipmentPresenter` |
| **Events** | `EquipmentRegistered` |

Cross-BC: `LinkEquipmentToOrder` (OrderFulfillment) → `EquipmentLinkedToOrder`.

### Identity
Каркас папок. POS login и Filament auth — в Presentation (`PosController`, Filament guards).

### OrderFulfillment (документы и read models)
| Тип | Реализовано |
|-----|-------------|
| **Commands** | `GenerateDocument` (receipt, handover_act) |
| **Queries** | `GetPosDashboard` |
| **Read models** | `OrderDocumentReadModelBuilder`, `PosOrderReadModelBuilder` |
| **Events** | `DocumentGenerated` |
| **Port** | `PdfRendererInterface` → DomPDF |

## Правила

- Handler → `Domain/{BC}/Repository/*Interface`, не Eloquent
- Cross-BC — только в CommandHandler
- Http/Filament вызывает Handler, не Domain напрямую

## DI

`app/Infrastructure/Shared/Provider/PersistenceServiceProvider.php`
