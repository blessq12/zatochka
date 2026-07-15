<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class OrderModel extends Model
{
    protected $table = 'orders';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'client_id',
        'status',
        'estimated_amount',
        'estimated_currency',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_amount' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }
}
