# 📦 Расширенная система склада: Анализ архитектуры

## 🎯 **Концепция расширения**

### **Основные принципы:**

1. **Филиал = Склад** - упрощение учёта
2. **Категоризация** - структурирование материалов
3. **Ценовая политика** - закупочная и розничная цена
4. **Трейсабельность** - связь с заказами и ремонтами

---

## 🏗️ **Новая архитектура**

### **Схема связей:**

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Company   │───▶│   Branch    │───▶│   Warehouse │
│             │    │             │    │   (Склад)   │
└─────────────┘    └─────────────┘    └─────────────┘
                           │                   │
                           │                   │
                           ▼                   ▼
                    ┌─────────────┐    ┌─────────────┐
                    │    Order    │    │StockItem    │
                    │             │    │(Единица     │
                    └─────────────┘    │ склада)     │
                                       └─────────────┘
                                                │
                                                ▼
                                       ┌─────────────┐
                                       │StockMovement│
                                       │(Движение)   │
                                       └─────────────┘
```

---

## 📊 **Новая структура БД**

### **1. Warehouse (Склад)**

```sql
CREATE TABLE warehouses (
    id BIGINT PRIMARY KEY,
    branch_id BIGINT UNIQUE, -- Один филиал = один склад
    name VARCHAR(255),
    description TEXT,
    is_active BOOLEAN DEFAULT true,
    is_deleted BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);
```

### **2. StockCategory (Категория материалов)**

```sql
CREATE TABLE stock_categories (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255), -- "Расходники", "Запчасти", "Инструменты"
    description TEXT,
    color VARCHAR(7), -- HEX цвет для UI
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    is_deleted BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **3. StockItem (Единица склада)**

```sql
CREATE TABLE stock_items (
    id BIGINT PRIMARY KEY,
    warehouse_id BIGINT, -- Привязка к складу
    category_id BIGINT, -- Категория материала
    name VARCHAR(255),
    sku VARCHAR(100) UNIQUE,
    description TEXT,

    -- Цены
    purchase_price DECIMAL(10,2), -- Закупочная цена
    retail_price DECIMAL(10,2),   -- Розничная цена

    -- Остатки
    quantity INT DEFAULT 0,
    min_stock INT DEFAULT 0,
    unit VARCHAR(20), -- "шт", "кг", "м"

    -- Метаданные
    supplier VARCHAR(255), -- Поставщик
    manufacturer VARCHAR(255), -- Производитель
    model VARCHAR(255), -- Модель/артикул

    -- Статус
    is_active BOOLEAN DEFAULT true,
    is_deleted BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (category_id) REFERENCES stock_categories(id)
);
```

### **4. StockMovement (Движение товаров)**

```sql
CREATE TABLE stock_movements (
    id BIGINT PRIMARY KEY,
    stock_item_id BIGINT,
    warehouse_id BIGINT,
    movement_type ENUM('in', 'out', 'transfer', 'adjustment', 'return'),
    quantity INT,

    -- Связи
    order_id BIGINT NULL, -- Заказ (если списание под заказ)
    repair_id BIGINT NULL, -- Ремонт (если списание под ремонт)
    supplier_id BIGINT NULL, -- Поставщик (если приход)

    -- Цены
    unit_price DECIMAL(10,2), -- Цена за единицу на момент движения
    total_amount DECIMAL(10,2), -- Общая сумма

    -- Метаданные
    description TEXT,
    reference_number VARCHAR(100), -- Номер накладной/счёта
    movement_date TIMESTAMP,

    -- Аудит
    created_by BIGINT, -- Пользователь, создавший движение
    created_at TIMESTAMP,

    FOREIGN KEY (stock_item_id) REFERENCES stock_items(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (repair_id) REFERENCES repairs(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

---

## 🔄 **Бизнес-процессы**

### **1. Приход товара (Stock In)**

```
Поставщик → Накладная → Склад → StockMovement(type='in')
```

-   Создаётся `StockMovement` с `type='in'`
-   Увеличивается `quantity` в `StockItem`
-   Фиксируется закупочная цена
-   Связь с поставщиком

### **2. Списание под заказ (Stock Out)**

```
Заказ → Склад → StockMovement(type='out') → Уменьшение остатков
```

-   Проверка достаточности остатков
-   Создание `StockMovement` с `type='out'`
-   Уменьшение `quantity` в `StockItem`
-   Связь с заказом для трейсабельности

### **3. Перемещение между складами (Transfer)**

```
Склад А → StockMovement(type='transfer') → Склад Б
```

-   Уменьшение на складе-отправителе
-   Увеличение на складе-получателе
-   Два `StockMovement` записи

### **4. Корректировка (Adjustment)**

```
Инвентаризация → StockMovement(type='adjustment')
```

-   Ручное изменение остатков
-   Обязательное указание причины
-   Аудит изменений

---

## 💡 **Преимущества новой архитектуры**

### **1. Упрощение учёта**

-   **Филиал = Склад** - нет путаницы с привязками
-   **Единая точка входа** для всех операций
-   **Прозрачность** движения товаров

### **2. Гибкость категоризации**

-   **Группировка** по типам материалов
-   **Отчётность** по категориям
-   **Цветовая индикация** в UI

### **3. Ценовая политика**

-   **Закупочная цена** - для расчёта себестоимости
-   **Розничная цена** - для продаж
-   **Маржинальность** - автоматический расчёт

### **4. Трейсабельность**

-   **Связь с заказами** - что ушло на ремонт
-   **Связь с ремонтами** - детальный учёт
-   **История движений** - полный аудит

---

## 🚧 **Что нужно реализовать**

### **1. Доменные сущности:**

```php
// Warehouse (агрегат)
class Warehouse extends AggregateRoot
{
    private WarehouseId $id;
    private BranchId $branchId;
    private WarehouseName $name;
    private Description $description;
    private bool $isActive;

    public function addStockItem(StockItem $item): void
    public function removeStockItem(StockItemId $itemId): void
    public function transferItem(StockItemId $itemId, WarehouseId $targetWarehouseId): void
}

// StockItem (сущность)
class StockItem
{
    private StockItemId $id;
    private WarehouseId $warehouseId;
    private CategoryId $categoryId;
    private StockItemName $name;
    private SKU $sku;
    private Money $purchasePrice;
    private Money $retailPrice;
    private Quantity $quantity;
    private MinStock $minStock;
    private Unit $unit;

    public function addStock(Quantity $amount, Money $unitPrice): void
    public function deductStock(Quantity $amount): void
    public function adjustStock(Quantity $newQuantity, string $reason): void
}

// StockMovement (сущность)
class StockMovement
{
    private StockMovementId $id;
    private StockItemId $stockItemId;
    private WarehouseId $warehouseId;
    private MovementType $type;
    private Quantity $quantity;
    private Money $unitPrice;
    private Money $totalAmount;
    private ?OrderId $orderId;
    private ?RepairId $repairId;
    private Description $description;
    private DateTimeImmutable $movementDate;
}
```

### **2. Value Objects:**

```php
class WarehouseName { private string $value; }
class CategoryName { private string $value; }
class SKU { private string $value; } // уникальный код
class Money { private float $amount, private string $currency; }
class Quantity { private int $value; }
class Unit { private string $value; } // "шт", "кг", "м"
class MovementType { private string $value; } // enum
class Description { private string $value; }
```

### **3. Доменные сервисы:**

```php
class WarehouseService
{
    public function createWarehouse(CreateWarehouseRequest $request): Warehouse
    public function addStockItem(WarehouseId $warehouseId, CreateStockItemRequest $request): StockItem
    public function transferItem(TransferItemRequest $request): void
    public function getStockReport(WarehouseId $warehouseId): StockReport
}

class StockMovementService
{
    public function recordMovement(RecordMovementRequest $request): StockMovement
    public function getMovementHistory(StockItemId $itemId): MovementHistory
    public function calculateStockValue(WarehouseId $warehouseId): Money
}
```

### **4. События:**

```php
class StockItemAdded extends DomainEvent
class StockItemMoved extends DomainEvent
class StockLevelChanged extends DomainEvent
class LowStockAlert extends DomainEvent
```

---

## 📈 **Отчётность и аналитика**

### **1. По складам:**

-   Остатки по категориям
-   Стоимость запасов
-   Движение товаров
-   Топ расходуемых материалов

### **2. По категориям:**

-   Расходники vs Запчасти
-   Себестоимость vs Розница
-   Оборачиваемость
-   Прибыльность

### **3. По времени:**

-   Месячные отчёты
-   Сезонность спроса
-   Прогнозирование закупок
-   Анализ поставщиков

---

## 🎯 **Приоритеты реализации**

### **Высокий:**

1. **Warehouse + StockItem** - основа системы
2. **StockMovement** - учёт движений
3. **Категории** - структурирование

### **Средний:**

4. **Ценовая политика** - закупочная/розничная
5. **Связи с заказами** - трейсабельность
6. **Отчётность** - аналитика

### **Низкий:**

7. **Перемещения между складами** - межфилиальные
8. **Поставщики** - расширенный учёт
9. **Прогнозирование** - ML для закупок

---

## 💡 **Рекомендации по реализации**

### **1. Поэтапность:**

-   **Этап 1:** Базовая структура (Warehouse + StockItem)
-   **Этап 2:** Движения и категории
-   **Этап 3:** Цены и отчётность
-   **Этап 4:** Расширенная функциональность

### **2. Миграция данных:**

-   Существующие `InventoryItem` → `StockItem`
-   Существующие `InventoryTransaction` → `StockMovement`
-   Создание `Warehouse` для каждого `Branch`

### **3. UI/UX:**

-   **Dashboard** с остатками по складам
-   **Цветовая индикация** категорий
-   **Графики** движения товаров
-   **Алерты** по критическим запасам

---

## 🎯 **Итог**

**Новая архитектура склада даёт:**

✅ **Упрощение** - филиал = склад  
✅ **Структурирование** - категории материалов  
✅ **Ценовую политику** - закупочная/розничная  
✅ **Трейсабельность** - связь с заказами  
✅ **Аналитику** - полная отчётность

**Это покрывает все потребности современного бизнеса по учёту материалов и запчастей.** 🚀
