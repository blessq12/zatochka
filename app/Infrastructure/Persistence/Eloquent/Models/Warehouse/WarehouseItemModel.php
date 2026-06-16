<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\Warehouse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseItemModel extends Model
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
        return $this->hasMany(StockMovementModel::class, 'warehouse_item_id');
    }
}
