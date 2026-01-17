<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderWorkMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'order_id',
        'warehouse_item_id',
        'name',
        'article',
        'category_name',
        'unit',
        'price',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:3',
    ];

    public function work()
    {
        return $this->belongsTo(OrderWork::class, 'work_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function warehouseItem()
    {
        return $this->belongsTo(WarehouseItem::class);
    }

    /**
     * Получить общую стоимость материала
     */
    public function getTotalAttribute(): float
    {
        return (float) ($this->quantity * $this->price);
    }
}
