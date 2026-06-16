<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\OrderFulfillment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderWorkModel extends Model
{
    protected $table = 'order_works';

    protected $fillable = [
        'order_id',
        'description',
        'price',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
