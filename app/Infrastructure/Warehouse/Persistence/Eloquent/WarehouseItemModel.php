<?php

namespace App\Infrastructure\Warehouse\Persistence\Eloquent;

use App\Domain\Warehouse\Enum\WarehouseItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseItemModel extends Model
{
    protected $table = 'warehouse_items';

    protected $fillable = [
        'name',
        'sku',
        'type',
        'quantity',
        'unit',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'type' => WarehouseItemType::class,
            'quantity' => 'decimal:3',
            'price' => 'decimal:2',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovementModel::class, 'warehouse_item_id');
    }
}
