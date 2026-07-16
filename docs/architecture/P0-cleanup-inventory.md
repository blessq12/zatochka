# P0 Inventory — Cleanup baseline

Locked decisions: kill Estimate · sync events only · Inventory command+event · BC verticals + TX UoW.

## 1. Application → Infrastructure leaks

| Location | Leak | Status |
|----------|------|--------|
| `MarkOrderReadyHandler` | Eloquent Workshop models | **Fixed P1** → `OrderContainerReadPort` |
| `SetWorkPriceHandler` | Eloquent + Sequential concrete | **Fixed P1** → `PerformedWorkRefPort` + `EntityIdGenerator` |
| `CreateOrderHandler` | Sequential concrete | **Fixed P1** → `EntityIdGenerator` |
| `SetOrderItemPriceHandler` | OrderItemModel + Sequential + Estimate | **P3 kill** |

`EntityIdGenerator` port bound to `SequentialEntityIdGenerator`.

Remaining Application→Model: **0** (Estimate killed in P3).

## 2. Cross-BC write violations (vertical breaks)

| Location | Violation | Target |
|----------|-----------|--------|
| `ReturnOrderToMasterHandler` | Order App mutates Workshop + Pricing repos in one handler | Order mutates+events only; listeners: Workshop reopen, Pricing clear |
| `ProductionTaskController::reject` | Workshop HTTP → Order `RejectOrderItemUnitsHandler` | Workshop event **or** Order command from POS via Order API (not Workshop controller) |
| Filament `OrderResource` write-off | Order UI → Inventory handler (acceptable write BC) | Keep Inventory command; ensure `MaterialWrittenOff` event; Order reacts only if needed via listener |
| Filament Order actions | Many Order commands OK | Split god-file in P4; no multi-BC orchestration in one action |

## 3. Estimate / ItemPrice kill-list — **DONE P3**

Removed Domain/App/Infra/HTTP/schema for Estimate, ItemPrice, Discount.
Runtime pricing write-path: **WorkPrice only**.

Remaining Application→Model: **0**.

## 4. Event matrix

Canonical catalog: [`domain-event-catalog.md`](./domain-event-catalog.md).

### Live (registered in PersistenceServiceProvider)

| Event | Listener | Effect |
|-------|----------|--------|
| `ReceptionCompleted` | `OpenProductionTasksOnReceptionCompleted` | Workshop opens task |
| `OrderMasterAssigned` | `OpenAndAssignTasksOnOrderMasterAssigned` | Workshop assign master |
| `OrderCancelled` | `CancelProductionTaskOnOrderCancelled` | Workshop cancel task |
| `OrderCancelled` | `ClearWorkPricesOnOrderCancelled` | Pricing clear (**P2**) |
| `OrderReturnedToMaster` | `ReopenProductionTaskOnOrderReturnedToMaster` | Workshop reopen (**P2**) |
| `OrderReturnedToMaster` | `ClearWorkPricesOnOrderReturnedToMaster` | Pricing clear (**P2**) |
| `WorkStarted` | `MarkOrderInProgressOnWorkStarted` → App command | Order → in_progress |
| `ProductionCompleted` | `MarkOrderWorksCompletedOnProductionCompleted` → App command | Order → works_completed |

### P6 hygiene — DONE

- Dead event classes (never recorded): **0**
- Orphan listener `CreateEstimateOnProductionCompleted`: already gone with Estimate
- Published without listeners: classified as **FUTURE_HOOK** (kept, documented)
- Workshop open-task listeners: ID gen + idempotency moved into `OpenProductionTaskHandler` (`EntityIdGenerator`)

### P2 TX UoW

- Port `UnitOfWork` → `EloquentUnitOfWork`
- Entry handlers wrapped: ReturnToMaster, Cancel, AssignMaster, CompleteReception, StartWork, FinishProduction
- Sync listeners run inside parent TX (Laravel savepoints on nest)

### Remaining vertical debt

- `FinishProductionTaskHandler` still reads `OrderRepository` for work-completeness check
- `AssignOrderMasterHandler` still uses `User` Eloquent

## P4 UI thin — DONE

- Filament: `OrderPresentation` + `OrderMutationActions`; resource ~582 LOC
- POS: composable + Header/Context/Works; page ~1010 (mostly CSS)
- Reject: Order API (`auth:sanctum,master`); Workshop reject removed
- IDs: CreateOrder items + WriteOffMaterial movement generated in Application handlers

## P5 ServiceType Strategy — DONE

- Order item build / Work attachment / Production completion / Work pricing presentation
- POS uses `work_target_mode` from API (`order_item` | `equipment_component`)

## P6 Event catalog hygiene — DONE

- See `docs/architecture/domain-event-catalog.md`
- LIVE spine unchanged; FUTURE_HOOK explicit; thin open-task listeners

## P7 Safety net — DONE

- Unit: Order/Workshop status transitions + aggregate FSM + completion policies
- Feature smoke: sharpening + repair → `works_completed`
- Feature TX: `CancelOrder` mid-chain listener fail → full rollback (order + production task)
- Run: `php artisan test`
