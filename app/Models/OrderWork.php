<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderWork extends Model
{
    use HasFactory;

    protected $table = 'works'; // Используем существующую таблицу works

    // Константы типов работ
    public const WORK_TYPE_SHARPENING = 'sharpening';

    public const WORK_TYPE_REPAIR = 'repair';

    public const WORK_TYPE_DIAGNOSTIC = 'diagnostic';

    protected $fillable = [
        'order_id',
        'work_type',
        'description',
        'quantity',
        'unit_price',
        'work_price',
        'materials_cost',
        'used_materials',
        'work_time_minutes',
        'is_deleted',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'work_price' => 'decimal:2',
        'materials_cost' => 'decimal:2',
        'used_materials' => 'array',
        'work_time_minutes' => 'integer',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Связь с товарами склада (списанные материалы)
     */
    public function warehouseItems()
    {
        return $this->belongsToMany(WarehouseItem::class, 'work_warehouse_items', 'work_id', 'warehouse_item_id')
            ->withPivot('quantity', 'price', 'notes')
            ->withTimestamps();
    }

    // Scope для активных работ
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Scope для работ по типу
    public function scopeByWorkType($query, string $workType)
    {
        return $query->where('work_type', $workType);
    }

    // Статические методы для получения доступных типов работ
    public static function getAvailableWorkTypes(): array
    {
        return [
            self::WORK_TYPE_SHARPENING => 'Заточка',
            self::WORK_TYPE_REPAIR => 'Ремонт',
            self::WORK_TYPE_DIAGNOSTIC => 'Диагностика',
        ];
    }

    /**
     * Рассчитать стоимость работы для заточки
     */
    public function calculateSharpeningPrice(): float
    {
        if ($this->work_type !== self::WORK_TYPE_SHARPENING) {
            return 0;
        }

        if ($this->quantity && $this->unit_price) {
            return (float) ($this->quantity * $this->unit_price);
        }

        return (float) ($this->work_price ?? 0);
    }

    /**
     * Рассчитать общую стоимость работы (работа + материалы)
     */
    public function calculateTotalCost(): float
    {
        $workPrice = (float) ($this->work_price ?? 0);
        $materialsCost = (float) ($this->materials_cost ?? 0);

        return $workPrice + $materialsCost;
    }

    /**
     * Проверить, является ли работа заточкой
     */
    public function isSharpening(): bool
    {
        return $this->work_type === self::WORK_TYPE_SHARPENING;
    }

    /**
     * Проверить, является ли работа ремонтом
     */
    public function isRepair(): bool
    {
        return $this->work_type === self::WORK_TYPE_REPAIR;
    }

    /**
     * Проверить, является ли работа диагностикой
     */
    public function isDiagnostic(): bool
    {
        return $this->work_type === self::WORK_TYPE_DIAGNOSTIC;
    }

    /**
     * Boot метод для автоматического расчета стоимости заточки
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($work) {
            // Автоматически рассчитываем work_price для заточки
            if ($work->work_type === self::WORK_TYPE_SHARPENING && $work->quantity && $work->unit_price) {
                $work->work_price = $work->quantity * $work->unit_price;
            }
        });
    }
}
