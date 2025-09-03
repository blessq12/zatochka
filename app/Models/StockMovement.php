<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_item_id',
        'warehouse_id',
        'movement_type',
        'quantity',
        'order_id',
        'repair_id',
        'supplier',
        'unit_price',
        'total_amount',
        'description',
        'reference_number',
        'movement_date',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    // Константы для типов движения
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_RETURN = 'return';

    // Связи
    public function stockItem()
    {
        return $this->belongsTo(StockItem::class, 'stock_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->hasOneThrough(Branch::class, Warehouse::class, 'id', 'id', 'warehouse_id', 'branch_id');
    }

    // Scope для движений по типу
    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    // Scope для движений по складу
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    // Scope для движений по товару
    public function scopeByStockItem($query, $stockItemId)
    {
        return $query->where('stock_item_id', $stockItemId);
    }

    // Scope для движений по заказу
    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    // Scope для движений по ремонту
    public function scopeByRepair($query, $repairId)
    {
        return $query->where('repair_id', $repairId);
    }

    // Scope для движений по дате
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('movement_date', $date);
    }

    // Scope для движений за период
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    // Методы
    public function isIncoming(): bool
    {
        return in_array($this->movement_type, [self::TYPE_IN, self::TYPE_RETURN]);
    }

    public function isOutgoing(): bool
    {
        return in_array($this->movement_type, [self::TYPE_OUT, self::TYPE_TRANSFER]);
    }

    public function isAdjustment(): bool
    {
        return $this->movement_type === self::TYPE_ADJUSTMENT;
    }

    public function isTransfer(): bool
    {
        return $this->movement_type === self::TYPE_TRANSFER;
    }

    // Получить связанный заказ (если есть)
    public function getRelatedOrder()
    {
        return $this->order;
    }

    // Получить связанный ремонт (если есть)
    public function getRelatedRepair()
    {
        return $this->repair;
    }

    // Получить информацию о создателе
    public function getCreatorInfo()
    {
        return $this->createdBy;
    }

    // Получить описание типа движения
    public function getMovementTypeDescription(): string
    {
        $types = [
            self::TYPE_IN => 'Приход',
            self::TYPE_OUT => 'Расход',
            self::TYPE_TRANSFER => 'Перемещение',
            self::TYPE_ADJUSTMENT => 'Корректировка',
            self::TYPE_RETURN => 'Возврат',
        ];

        return $types[$this->movement_type] ?? 'Неизвестно';
    }

    // Получить цвет для типа движения (для UI)
    public function getMovementTypeColor(): string
    {
        $colors = [
            self::TYPE_IN => 'success', // Зелёный
            self::TYPE_OUT => 'danger',  // Красный
            self::TYPE_TRANSFER => 'info', // Синий
            self::TYPE_ADJUSTMENT => 'warning', // Жёлтый
            self::TYPE_RETURN => 'primary', // Синий
        ];

        return $colors[$this->movement_type] ?? 'secondary';
    }

    // Проверить, связано ли движение с заказом
    public function isOrderRelated(): bool
    {
        return !is_null($this->order_id);
    }

    // Проверить, связано ли движение с ремонтом
    public function isRepairRelated(): bool
    {
        return !is_null($this->repair_id);
    }

    // Получить общую сумму движения
    public function getTotalAmount(): float
    {
        if ($this->total_amount) {
            return $this->total_amount;
        }

        if ($this->unit_price && $this->quantity) {
            return $this->unit_price * $this->quantity;
        }

        return 0;
    }
}
