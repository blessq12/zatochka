<?php

namespace App\Infrastructure\Delivery\Model;

use Illuminate\Database\Eloquent\Model;

final class DeliveryRequestModel extends Model
{
    protected $table = 'delivery_requests';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'order_id',
        'status',
        'pickup',
        'city',
        'street',
        'building',
        'apartment',
        'comment',
        'courier_id',
        'courier_assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'pickup' => 'boolean',
            'courier_assigned_at' => 'datetime',
        ];
    }
}
