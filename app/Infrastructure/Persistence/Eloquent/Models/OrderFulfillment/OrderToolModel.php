<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\OrderFulfillment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderToolModel extends Model
{
    protected $table = 'order_tools';

    protected $fillable = [
        'order_id',
        'tool_type',
        'quantity',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
