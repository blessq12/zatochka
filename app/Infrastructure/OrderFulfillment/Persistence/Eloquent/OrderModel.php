<?php

namespace App\Infrastructure\OrderFulfillment\Persistence\Eloquent;

use App\Domain\OrderFulfillment\Enum\OrderSource;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderModel extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'status',
        'service_types',
        'urgency',
        'is_warranty',
        'needs_delivery',
        'delivery_address',
        'problem_description',
        'internal_notes',
        'price',
        'source',
        'client_snapshot',
        'lead_id',
        'client_id',
        'equipment_id',
        'master_id',
        'manager_id',
        'branch_id',
        'warranty_parent_order_id',
        'taken_at',
        'ready_at',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'service_types' => 'array',
            'urgency' => OrderUrgency::class,
            'is_warranty' => 'boolean',
            'needs_delivery' => 'boolean',
            'price' => 'decimal:2',
            'source' => OrderSource::class,
            'client_snapshot' => 'array',
            'taken_at' => 'datetime',
            'ready_at' => 'datetime',
            'issued_at' => 'datetime',
        ];
    }

    public function works(): HasMany
    {
        return $this->hasMany(OrderWorkModel::class, 'order_id')->orderBy('sort_order');
    }

    public function tools(): HasMany
    {
        return $this->hasMany(OrderToolModel::class, 'order_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(OrderMaterialModel::class, 'order_id');
    }
}
