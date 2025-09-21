<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_item_id',
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
        // Поля для "заморозки" данных запчасти
        'part_name',
        'part_sku',
        'part_purchase_price',
        'part_retail_price',
        'part_unit',
        'part_supplier',
        'part_manufacturer',
        'part_model',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'movement_date' => 'datetime',
        'part_purchase_price' => 'decimal:2',
        'part_retail_price' => 'decimal:2',
    ];

    // Boot method для автоматического списания при создании движения типа 'out'
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movement) {
            // Автоматически заполняем данные запчасти при создании движения
            if ($movement->stock_item_id) {
                $stockItem = \App\Models\StockItem::find($movement->stock_item_id);
                if ($stockItem) {
                    $movement->part_name = $stockItem->name;
                    $movement->part_sku = $stockItem->sku;
                    $movement->part_purchase_price = $stockItem->purchase_price;
                    $movement->part_retail_price = $stockItem->retail_price;
                    $movement->part_unit = $stockItem->unit;
                    $movement->part_supplier = $stockItem->supplier;
                    $movement->part_manufacturer = $stockItem->manufacturer;
                    $movement->part_model = $stockItem->model;
                }
            }
        });

        static::created(function ($movement) {
            if ($movement->movement_type === self::TYPE_OUT && $movement->stock_item_id) {
                $stockItem = $movement->stockItem;
                if ($stockItem && $stockItem->hasStock($movement->quantity)) {
                    $stockItem->deductStock($movement->quantity);
                }
            }
        });
    }

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


    // Scope для движений по типу
    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    // Scope для движений по складу

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
        return ! is_null($this->order_id);
    }

    // Проверить, связано ли движение с ремонтом
    public function isRepairRelated(): bool
    {
        return ! is_null($this->repair_id);
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
