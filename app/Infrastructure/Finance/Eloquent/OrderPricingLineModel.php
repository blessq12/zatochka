<?php

namespace App\Infrastructure\Finance\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderPricingLineModel extends Model
{
    protected $table = 'order_pricing_lines';

    protected $fillable = [
        'pricing_id',
        'work_entry_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(OrderPricingModel::class, 'pricing_id');
    }
}
