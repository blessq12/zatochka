<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderWork extends Model
{
    use HasFactory;

    protected $table = 'works';

    protected $fillable = [
        'order_id',
        'description',
        'equipment_component_name',
        'equipment_component_serial_number',
        'quantity',
        'unit_price',
        'work_price',
        'materials_cost',
        'used_materials',
        'is_deleted',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'work_price' => 'decimal:2',
        'materials_cost' => 'decimal:2',
        'used_materials' => 'array',
        'is_deleted' => 'boolean',
    ];

    // Связи
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Связь с материалами работы (snapshot данных)
     */
    public function materials()
    {
        return $this->hasMany(OrderWorkMaterial::class, 'work_id');
    }

    /**
     * Старая связь для обратной совместимости (deprecated)
     * @deprecated Используйте materials() вместо warehouseItems()
     */
    public function warehouseItems()
    {
        return $this->belongsToMany(WarehouseItem::class, 'work_warehouse_items', 'work_id', 'warehouse_item_id')
            ->withPivot('quantity', 'price', 'notes')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public function calculateTotalCost(): float
    {
        $workPrice = (float) ($this->work_price ?? 0);
        $materialsCost = (float) ($this->materials_cost ?? 0);

        return $workPrice + $materialsCost;
    }
}
