<?php

namespace App\Infrastructure\OrderFulfillment\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderToolModel extends Model
{
    protected $table = 'order_tools';

    protected $fillable = [
        'order_id',
        'name',
        'tool_type',
        'quantity',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
