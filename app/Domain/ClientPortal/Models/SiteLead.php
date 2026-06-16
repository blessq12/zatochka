<?php

namespace App\Domain\ClientPortal\Models;

use App\Domain\OrderFulfillment\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteLead extends Model
{
    protected $table = 'site_leads';

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'service_types',
        'comment',
        'needs_delivery',
        'delivery_address',
        'converted',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'service_types' => 'array',
            'needs_delivery' => 'boolean',
            'converted' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
