<?php

namespace App\Domain\OrderFulfillment\Models;

use App\Domain\Catalog\Models\Branch;
use App\Domain\ClientPortal\Models\Client;
use App\Domain\ClientPortal\Models\Review;
use App\Domain\ClientPortal\Models\SiteLead;
use App\Domain\Equipment\Models\Equipment;
use App\Domain\OrderFulfillment\Enums\OrderSource;
use App\Domain\OrderFulfillment\Enums\OrderStatus;
use App\Domain\OrderFulfillment\Enums\OrderUrgency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(SiteLead::class, 'lead_id');
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function warrantyParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'warranty_parent_order_id');
    }

    public function tools(): HasMany
    {
        return $this->hasMany(OrderTool::class, 'order_id');
    }

    public function works(): HasMany
    {
        return $this->hasMany(OrderWork::class, 'order_id')->orderBy('sort_order');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(OrderMaterial::class, 'order_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'order_id');
    }

    public function clientName(): string
    {
        return $this->client_snapshot['full_name']
            ?? $this->client?->full_name
            ?? '';
    }

    public function clientPhone(): string
    {
        return $this->client_snapshot['phone']
            ?? $this->client?->phone
            ?? '';
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, [OrderStatus::Issued, OrderStatus::Cancelled], true);
    }
}
