<?php

namespace App\Domain\OrderFulfillment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTool extends Model
{
    protected $table = 'order_tools';

    protected $fillable = [
        'order_id',
        'tool_type',
        'quantity',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
