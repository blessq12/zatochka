<?php

namespace App\Infrastructure\Order\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class OrderDraftModel extends Model
{
    protected $table = 'order_drafts';

    protected $fillable = [
        'source',
        'status',
        'client_id',
        'full_name',
        'phone',
        'service_type',
        'payload',
        'needs_delivery',
        'delivery_address',
        'comment',
        'order_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'needs_delivery' => 'boolean',
    ];
}
