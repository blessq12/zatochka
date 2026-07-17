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

| Location | Violation | Status |
|----------|-----------|--------|
| `ReturnOrderToMasterHandler` | Order App mutates Workshop + Pricing | **Closed** — Order + events only |
| `ProductionTaskController::reject` | Workshop HTTP → Order handler | **Closed** — Order API |
| Sync provisioning CRM/Equipment from CreateOrder | Order App → foreign handlers | **Closed** — UI/API pre-registers; CreateOrder IDs only |
| `RepairWorkAttachmentStrategy` → Equipment repo | Workshop App → Equipment write stack | **Closed** → `EquipmentComponentBelongingPort` |
| Workshop/Pricing ports hydrate `Order` aggregate | foreign write stack via ports | **Closed** — DTO / read-query gates |
| Filament Pricing/Inventory actions in Order folder | wrong BC placement | **Closed** — `Filament/Pricing`, `Filament/Inventory` actions |

## 3. Estimate / ItemPrice kill-list — **DONE P3**

Removed Domain/App/Infra/HTTP/schema for Estimate, ItemPrice, Discount.
Runtime pricing write-path: **WorkPrice only**.
Estimate leftover Query/Controller/Presenter: **deleted**.

Remaining Application→Model: **0**.

## 4. Event matrix

Canonical catalog: [`domain-event-catalog.md`](./domain-event-catalog.md).

### Live (registered in PersistenceServiceProvider)

| Event | Listener | Effect |
|-------|----------|--------|
| `ReceptionCompleted` | `OpenProductionTasksOnReceptionCompleted` | Workshop opens task |
| `OrderMasterAssigned` | `OpenAndAssignTasksOnOrderMasterAssigned` → `EnsureProductionTaskOpenedAndAssignedHandler` | Workshop open + assign |
| `OrderCancelled` | `CancelProductionTaskOnOrderCancelled` | Workshop cancel task |
| `OrderCancelled` | `ClearWorkPricesOnOrderCancelled` | Pricing clear |
| `OrderReturnedToMaster` | `ReopenProductionTaskOnOrderReturnedToMaster` | Workshop reopen |
| `OrderReturnedToMaster` | `ClearWorkPricesOnOrderReturnedToMaster` | Pricing clear |
| `WorkStarted` | `MarkOrderInProgressOnWorkStarted` → App command | Order → in_progress |
| `ProductionCompleted` | `MarkOrderWorksCompletedOnProductionCompleted` → App command | Order → works_completed |

### P2 / extended TX UoW

- Port `UnitOfWork` → `EloquentUnitOfWork`
- Entry handlers wrapped: ReturnToMaster, Cancel, AssignMaster, CompleteReception, StartWork, FinishProduction, **CreateOrder**, **SetOrderWorkPrices**, **WriteOffMaterial**, **MarkOrderReady**
- Sync listeners run inside parent TX (Laravel savepoints on nest)

## 5. Vertical isolation uplift (to 5.0) — DONE

- CreateOrder: no `ClientProvisioningPort` / `EquipmentProvisioningPort`
- Workshop: `OrderProductionContextPort` → `OrderProductionContextDTO` (no `Order` entity / `OrderRepository`)
- Workshop: `EquipmentComponentBelongingPort` for repair attachment
- Pricing: `EloquentOrderPricingGatePort` via Order read-query (no `OrderRepository`)
- Identity vertical: Domain/Application/Infrastructure + Filament via Register/Update/ChangePassword commands
- Order `MasterDirectoryPort` → Identity `StaffUserReadPort`
- Arch tests: `tests/Unit/Architecture/VerticalIsolationTest.php`

## P4 UI thin — DONE

- Filament: `OrderPresentation` + `OrderMutationActions`; Pricing/Inventory actions owned by their BC folders
- POS: composable + Header/Context/Works
- Reject: Order API (`auth:sanctum,master`); Workshop reject removed

## P5 ServiceType Strategy — DONE

- Order item build / Work attachment / Production completion / Work pricing presentation
- POS uses `work_target_mode` from API (`order_item` | `equipment_component`)

## P6 Event catalog hygiene — DONE

- See `docs/architecture/domain-event-catalog.md`
- LIVE spine unchanged; FUTURE_HOOK explicit; listeners thin

## P7 Safety net — DONE

- Unit: Order/Workshop status transitions + aggregate FSM + completion policies + architecture isolation
- Feature smoke: sharpening + repair → `works_completed`
- Feature TX: `CancelOrder` mid-chain listener fail → full rollback
- Run: `php artisan test`
