<?php

namespace App\Domain\Warehouse\Models;

use App\Domain\OrderFulfillment\Models\Order;
use App\Domain\Warehouse\Enums\StockMovementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
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
        return $this->belongsTo(WarehouseItem::class, 'warehouse_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
