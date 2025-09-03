# Список ошибок PHPStan (composer analyse)

## Domain Entities

### Branch.php

-   **Line 107**: Вызов конструктора `App\Domain\Company\Events\BranchCreated` с 2 параметрами, требуется 3
    -   🪪 arguments.count

### Company.php

-   **Line 45**: Deprecated в PHP 8.1: обязательный параметр `$legalAddress` после опционального `$ogrn`
    -   🪪 parameter.requiredAfterOptional

### StockCategory.php

-   **Line 14**: Свойство `$id` имеет неизвестный класс `App\Domain\Inventory\ValueObjects\CategoryId`
    -   🪪 class.notFound
-   **Line 25**: Параметр `$id` в `__construct()` имеет тип `CategoryId`
    -   🪪 class.notFound
-   **Line 43**: Параметр `$id` в `create()` имеет тип `CategoryId`
    -   🪪 class.notFound
-   **Line 61**: Параметр `$id` в `reconstitute()` имеет тип `CategoryId`
    -   🪪 class.notFound
-   **Line 80**: Метод `id()` возвращает `CategoryId`
    -   🪪 class.notFound

### Warehouse.php

-   **Line 15**: Свойство `$id` имеет неизвестный класс `App\Domain\Inventory\ValueObjects\WarehouseId`
    -   🪪 class.notFound
-   **Line 16**: Свойство `$branchId` имеет неизвестный класс `App\Domain\Inventory\ValueObjects\BranchId`
    -   🪪 class.notFound
-   **Line 25**: Параметр `$id` конструктора имеет тип `WarehouseId`
    -   🪪 class.notFound
-   **Line 26**: Параметр `$branchId` конструктора имеет тип `BranchId`
    -   🪪 class.notFound
-   **Line 41**: Параметр `$id` в `create()` имеет тип `WarehouseId`
    -   🪪 class.notFound
-   **Line 42**: Параметр `$branchId` в `create()` имеет тип `BranchId`
    -   🪪 class.notFound
-   **Line 59**: Параметр `$id` в `reconstitute()` имеет тип `WarehouseId`
    -   🪪 class.notFound
-   **Line 60**: Параметр `$branchId` в `reconstitute()` имеет тип `BranchId`
    -   🪪 class.notFound
-   **Line 78**: Метод `id()` возвращает `WarehouseId`
    -   🪪 class.notFound
-   **Line 83**: Метод `branchId()` возвращает `BranchId`
    -   🪪 class.notFound
-   **Line 190**: Параметр `$branchId` в `assignToBranch()` имеет тип `BranchId`
    -   🪪 class.notFound

## Repository Interfaces

### StockCategoryRepositoryInterface.php

-   **Line 10**: В `findById()` тип параметра `$id` — `CategoryId`
    -   🪪 class.notFound
-   **Line 22**: В `delete()` тип параметра `$id` — `CategoryId`
    -   🪪 class.notFound
-   **Line 24**: В `exists()` тип параметра `$id` — `CategoryId`
    -   🪪 class.notFound

### StockItemRepositoryInterface.php

-   **Line 13**: В `findById()` тип `$id` — `StockItemId`
    -   🪪 class.notFound
-   **Line 17**: В `findByWarehouseId()` тип `$warehouseId` — `WarehouseId`
    -   🪪 class.notFound
-   **Line 19**: В `findByCategoryId()` тип `$categoryId` — `CategoryId`
    -   🪪 class.notFound
-   **Line 21**: В `findByWarehouseAndCategory()` типы `$categoryId`, `$warehouseId` — VO
    -   🪪 class.notFound
-   **Line 33**: В `delete()` тип `$id` — `StockItemId`
    -   🪪 class.notFound
-   **Line 35**: В `exists()` тип `$id` — `StockItemId`
    -   🪪 class.notFound
-   **Line 39**: В `existsBySkuAndWarehouse()` тип `$warehouseId` — `WarehouseId`
    -   🪪 class.notFound

### StockMovementRepositoryInterface.php

-   **Line 13**: В `findById()` тип `$id` — `UuidValueObject`
    -   🪪 class.notFound
-   **Line 15**: В `findByStockItemId()` тип `$stockItemId` — `StockItemId`
    -   🪪 class.notFound
-   **Line 17**: В `findByWarehouseId()` тип `$warehouseId` — `WarehouseId`
    -   🪪 class.notFound
-   **Line 19**: В `findByOrderId()` тип `$orderId` — `UuidValueObject`
    -   🪪 class.notFound
-   **Line 21**: В `findByRepairId()` тип `$repairId` — `UuidValueObject`
    -   🪪 class.notFound
-   **Line 27**: В `findByStockItemAndDateRange()` тип `$stockItemId` — `StockItemId`
    -   🪪 class.notFound
-   **Line 35**: В `delete()` тип `$id` — `UuidValueObject`
    -   🪪 class.notFound
-   **Line 37**: В `exists()` тип `$id` — `UuidValueObject`
    -   🪪 class.notFound
-   **Line 39**: В `getMovementHistory()` тип `$stockItemId` — `StockItemId`
    -   🪪 class.notFound

### WarehouseRepositoryInterface.php

-   **Line 11**: В `findById()` тип `$id` — `WarehouseId`
    -   🪪 class.notFound
-   **Line 13**: В `findByBranchId()` тип `$branchId` — `BranchId`
    -   🪪 class.notFound
-   **Line 21**: В `delete()` тип `$id` — `WarehouseId`
    -   🪪 class.notFound
-   **Line 23**: В `exists()` тип `$id` — `WarehouseId`
    -   🪪 class.notFound
-   **Line 25**: В `existsByBranchId()` тип `$branchId` — `BranchId`
    -   🪪 class.notFound

## Filament Resources

### StockCategoryResource.php

-   **Line 129**: Вызов `CategoryId::fromString()` несуществующего класса
    -   🪪 class.notFound
-   **Line 165**: Вызов `CategoryId::fromString()` несуществующего класса
    -   🪪 class.notFound
-   **Line 182**: Вызов `CategoryId::fromString()` несуществующего класса
    -   🪪 class.notFound

### CreateStockCategory.php

-   **Line 25**: Вызов `CategoryId::fromString()`
    -   🪪 class.notFound

### StockItemResource.php

-   **Line 272**: Вызов `StockItemId::fromString()`
    -   🪪 class.notFound
-   **Line 314**: Вызов `StockItemId::fromString()`
    -   🪪 class.notFound
-   **Line 331**: Вызов `StockItemId::fromString()`
    -   🪪 class.notFound

### CreateStockItem.php

-   **Line 31**: `StockItemId::fromString()`
    -   🪪 class.notFound
-   **Line 32**: `WarehouseId::fromString()`
    -   🪪 class.notFound
-   **Line 33**: `CategoryId::fromString()`
    -   🪪 class.notFound

### EditUser.php

-   **Line 25**: `UserId::fromString()`
    -   🪪 class.notFound
-   **Line 47**: Вызов `Log::info()` без импорта фасада
    -   🪪 class.notFound
-   **Line 52**: Вызов `Log::info()` без импорта фасада
    -   🪪 class.notFound

### WarehouseResource.php

-   **Line 136**: `WarehouseId::fromString()`
    -   🪪 class.notFound
-   **Line 138**: `BranchId::fromString()`
    -   🪪 class.notFound
-   **Line 173**: `WarehouseId::fromString()`
    -   🪪 class.notFound
-   **Line 190**: `WarehouseId::fromString()`
    -   🪪 class.notFound

### CreateWarehouse.php

-   **Line 26**: `WarehouseId::fromString()`
    -   🪪 class.notFound
-   **Line 28**: `BranchId::fromString()`
    -   🪪 class.notFound

## Controllers

### TestEventController.php

-   **Line 27**: `UserId::new()` на удаленном классе
    -   🪪 class.notFound

## Итого

**Найдено 70 ошибок**

## Приоритеты исправления

1. **Domain Entities** - заменить типы свойств с VO на `int`
2. **Repository Interfaces** - заменить типы параметров с VO на `int`
3. **Filament Resources** - убрать вызовы `fromString()` и заменить на `int`
4. **Controllers** - исправить вызовы удаленных методов
5. **Events** - исправить конструкторы и параметры
