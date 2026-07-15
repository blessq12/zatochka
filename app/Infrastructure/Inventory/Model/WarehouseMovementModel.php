<?php

namespace App\Infrastructure\Inventory\Model;

use Illuminate\Database\Eloquent\Model;

final class WarehouseMovementModel extends Model
{
    protected $table = 'warehouse_movements';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'stock_item_id',
        'type',
        'quantity',
        'comment',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'string',
            'occurred_at' => 'datetime',
        ];
    }
}
