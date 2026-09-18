<?php

namespace App\Infrastructure\Order\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class OrderModel extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'client_id',
        'master_id',
        'billing_type',
        'urgency',
        'estimated_cost',
        'needs_delivery',
        'delivery_address',
        'status',
    ];

    protected $casts = [
        'needs_delivery' => 'boolean',
        'estimated_cost' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id')->orderBy('position')->orderBy('id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(OrderReviewModel::class, 'order_id');
    }
}
