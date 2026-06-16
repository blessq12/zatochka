<?php

namespace App\Domain\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseItem extends Model
{
    protected $table = 'warehouse_items';

    protected $fillable = [
        'name',
        'sku',
        'category_name',
        'quantity',
        'unit',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'price' => 'decimal:2',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'warehouse_item_id');
    }
}
