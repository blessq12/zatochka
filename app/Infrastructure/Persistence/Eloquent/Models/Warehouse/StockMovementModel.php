<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\Warehouse;

use App\Domain\Warehouse\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementModel extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'warehouse_item_id',
        'type',
        'quantity',
        'comment',
        'user_id',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'quantity' => 'decimal:3',
        ];
    }

    public function warehouseItem(): BelongsTo
    {
        return $this->belongsTo(WarehouseItemModel::class, 'warehouse_item_id');
    }
}
