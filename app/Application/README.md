# Application Layer

Use cases по bounded contexts.

## Статус

### OrderFulfillment
Lifecycle, POS queries, цены, материалы, привязка оборудования, PDF.

| Тип | Реализовано |
|-----|-------------|
| **Commands** | `CreateOrder`, lifecycle, works/materials, `LinkEquipmentToOrder`, `GenerateDocument`, … |
| **Queries** | `GetPosOrders`, `GetPosOrderDetail`, `GetPosOrderCounts`, `GetPosDashboard` |
| **Read models** | `PosOrderReadModelBuilder`, `OrderDocumentReadModelBuilder` |
| **Port** | `PdfRendererInterface` → DomPDF |

### ClientPortal
| Тип | Реализовано |
|-----|-------------|
| **Commands** | `SubmitSiteLead`, `RegisterClient`, `UpdateClientProfile`, `SetClientPassword`, `SubmitReview`, `LinkGuestOrdersToClient`, `ApproveReview`, `RejectReview` |
| **Queries** | `GetClientProfile`, `GetClientOrders` (active/history), `GetClientOrderDetail` |
| **Presenters** | `ClientProfilePresenter`, `ClientOrderPresenter`, `ReviewPresenter` |

### Company
| Тип | Реализовано |
|-----|-------------|
| **Commands** | `SaveBranch` |
| **Queries** | `GetPublicSiteContent` → contacts, schedule, delivery_info, company, faq |

### Pricing
| Тип | Реализовано |
|-----|-------------|
| **Commands** | `SavePriceItem` |
| **Queries** | `GetPublicPriceList` → blocks + items с `prefix` |

### PublicSite (фасад)
| Тип | Реализовано |
|-----|-------------|
| **Queries** | `GetPublicBootstrap` — оркестрация `GetPublicSiteContent` + `GetPublicPriceList` |

Потребитель SPA: `resources/js/stores/bootstrapStore.js`.

### Warehouse
| Тип | Реализовано |
|-----|-------------|
| **Commands** | `ReceiveStock`, `WriteOffStock` |
| **Queries** | `SearchWarehouseItems` |
| **Presenters** | `WarehouseItemPresenter` |

### Equipment
| Тип | Реализовано |
|-----|-------------|
| **Commands** | `RegisterEquipment` |
| **Queries** | `SearchEquipment`, `GetEquipmentOrderHistory` |
| **Presenters** | `EquipmentPresenter` |

Cross-BC: `LinkEquipmentToOrder` (OrderFulfillment) → `EquipmentLinkedToOrder`.

### Identity
| Тип | Реализовано |
|-----|-------------|
| **Commands** | `RegisterMaster`, `UpdateMaster` |

POS login — в Presentation (`PosController`), без Application handler.

## Правила

- Handler → `Domain/{BC}/Repository/*Interface`, не Eloquent
- Cross-BC — только в CommandHandler или ReadModel Builder
- Http/Filament вызывает Handler, не Domain напрямую

## DI

`app/Infrastructure/Shared/Provider/PersistenceServiceProvider.php`
