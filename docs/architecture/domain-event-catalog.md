# Domain event catalog

Status legend:

- **LIVE** — registered listener in `PersistenceServiceProvider` (cross-BC reaction)
- **FUTURE_HOOK** — aggregate records + Application publishes; **no listener by design** (domain fact / reserved hook). Do not delete without product decision.

Dead classes (defined but never `record()`'d): **none** (P6 audit).

## LIVE (Order ↔ Workshop ↔ Pricing spine)

| Event | Listener(s) | Target BC effect |
|-------|-------------|------------------|
| `Order\ReceptionCompleted` | `OpenProductionTasksOnReceptionCompleted` | Workshop open task (idempotent) |
| `Order\OrderMasterAssigned` | `OpenAndAssignTasksOnOrderMasterAssigned` | Workshop open + assign master |
| `Order\OrderCancelled` | `CancelProductionTaskOnOrderCancelled` | Workshop cancel task |
| `Order\OrderCancelled` | `ClearWorkPricesOnOrderCancelled` | Pricing clear |
| `Order\OrderReturnedToMaster` | `ReopenProductionTaskOnOrderReturnedToMaster` | Workshop reopen |
| `Order\OrderReturnedToMaster` | `ClearWorkPricesOnOrderReturnedToMaster` | Pricing clear |
| `Workshop\WorkStarted` | `MarkOrderInProgressOnWorkStarted` | Order → in_progress |
| `Workshop\ProductionCompleted` | `MarkOrderWorksCompletedOnProductionCompleted` | Order → works_completed |

## FUTURE_HOOK — Order

- `OrderCreated`, `ClientAssigned`, `OrderItemAdded`
- `OrderItemUnitsRejected`
- `OrderClosed`, `OrderIssued`

## FUTURE_HOOK — Workshop

- `MasterAssigned`, `DiagnosisCompleted`, `WorkCompleted`, `ProductionCancelled`

## FUTURE_HOOK — Pricing / Inventory

- Inventory: `MaterialWrittenOff`, `MaterialReceived`, `StockChanged`  
  (write-off from UI = Inventory command; events are outbound facts)

## FUTURE_HOOK — CRM / Equipment / Finance / Delivery / Feedback

- CRM: `ClientRegistered`, `ClientUpdated`, `BonusAccrued`
- Equipment: `EquipmentRegistered`, `ComponentAdded`, `SerialNumberRegistered`
- Finance: `PaymentAccepted`, `RefundCreated`, `CashOperationRegistered`
- Delivery: `DeliveryRequested`, `CourierAssigned`, `EquipmentCollected`, `OrderDelivered`
- Feedback: `ReviewSubmitted`, `ReviewPublished`, `ReviewRejected`, `ReviewHidden`, `ReviewRestored`, `ReviewDeleted`

## Rules

1. New cross-BC write → register LIVE listener → thin Infrastructure adapter → Application command of **target** BC.
2. Do not invent listeners “just because event exists”; promote FUTURE_HOOK → LIVE with an explicit use-case.
3. Do not leave unregistered listener classes (orphan adapters).
