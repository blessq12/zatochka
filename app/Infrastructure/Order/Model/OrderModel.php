<?php

namespace App\Infrastructure\Order\Model;

use App\Infrastructure\CRM\Model\ClientModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class OrderModel extends Model
{
    protected $table = 'orders';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'number',
        'client_id',
        'status',
        'service_type',
        'billing_type',
        'urgency',
        'delivery_required',
        'defects',
        'internal_notes',
        'warranty_source_order_id',
        'assigned_master_id',
        'estimated_amount',
        'estimated_currency',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_required' => 'boolean',
            'estimated_amount' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id');
    }

    public function warrantySourceOrder(): BelongsTo
    {
        return $this->belongsTo(self::class, 'warranty_source_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }
}
